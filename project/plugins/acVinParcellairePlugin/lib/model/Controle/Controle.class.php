<?php
class Controle extends BaseControle implements InterfacePieceDocument
{
    protected $config = null;
    protected $parcellaire = null;
    protected $declarant_document = null;
    protected $piece_document = null;

    public function getConfig()
    {
        if (!$this->config) {
            $this->config = ControleConfiguration::getInstance();
        }
        return $this->config;
    }

    protected function initDocuments()
    {
        if (! isset($this->declarant_document)) {
            $this->declarant_document = new DeclarantDocument($this);
        }
        $this->piece_document = new PieceDocument($this);
    }

    public function initDoc($identifiant, $date, $type = ControleClient::TYPE_COUCHDB)
    {
        $this->identifiant = $identifiant;
        if (strpos($date, '-') === false) {
            throw new sfException('wrong date format y-m-d: '.$date);
        }
        $this->date = $date;
        $this->campagne = ConfigurationClient::getInstance()->buildCampagne($date);
        $this->set('_id', ControleClient::TYPE_COUCHDB."-".$identifiant."-".str_replace('-', '', $date));
        $this->initDocuments();
        $this->storeDeclarant();
        $this->initPotentielProductionProduits();
        $this->superficie_totale = $this->getSuperficieTotale();
    }

    public function getEtablissementObject() {

        return EtablissementClient::getInstance()->findByIdentifiant($this->identifiant);
    }

    public function storeDeclarant() {
        $this->initDocuments();
        $this->declarant_document->storeDeclarant();
        $etablissement = $this->getEtablissementObject();
        if($etablissement->exist('secteur')) {
            $this->document->secteur = $etablissement->secteur;
        }
        $this->liaisons_operateurs = $this->getLiaisonsCooperative();
    }

    public function getTypeTournee() {
        $t = $this->_get('type_tournee');
        if (!$t) {
            return ControleClient::CONTROLE_TYPE_SUIVI;
        }
        return $t;
    }

    public function getLiaisonsCooperative() {
        $e = EtablissementClient::getInstance()->findByCvi($this->declarant->cvi);
        if (!$e) {
            return [];
        }
        return $e->getLiaisonsOfType(EtablissementFamilles::FAMILLE_COOPERATIVE, true);
    }

    public function getLiaisonsLibellesArray() {
        $libelles = [];
        foreach($this->liaisons_operateurs as $liaison) {
            $libelles[] = $liaison->libelle_etablissement;
        }
        return $libelles;
    }

    public function hasLiaisons() {
        return count($this->getLiaisonsLibellesArray());
    }

    public function getLiaisonsLibellesString() {
        return implode(', ', $this->getLiaisonsLibellesArray());
    }

    public function getParcellaire()
    {
        if (!$this->parcellaire) {
            $this->parcellaire = ParcellaireClient::getInstance()->getLast($this->identifiant, acCouchdbClient::HYDRATE_JSON);
        }
        return $this->parcellaire;
    }

    public function getParcellaireParcelles()
    {
        $parcellaire = $this->getParcellaire();
        $parcelles = [];
        if ($parcellaire) foreach ($parcellaire->getParcelles() as $key => $parcelle) {
            if (!($parcelle->isRealProduit() && ParcellaireConfiguration::getInstance()->hasShowFilterProduitsConfiguration())) continue;
            if (ControleConfiguration::getInstance()->hasProduitFilter() && strpos($parcelle->produit_hash, ControleConfiguration::getInstance()->getProduitFilter()) === false) continue;
            $parcelles[$key] = $parcelle->getData();
            $parcelles[$key]->hasProblemExpirationCepage = $parcelle->hasProblemExpirationCepage();
            $parcelles[$key]->hasProblemEcartPieds = $parcelle->hasProblemEcartPieds();
            $parcelles[$key]->hasProblemCepageAutorise = $parcelle->hasProblemExpirationCepage();
            $parcelles[$key]->hasJeunesVignes = $parcelle->isJeunesVignes() && ParcellaireConfiguration::getInstance()->isJeunesVignesEnabled();
            $parcelles[$key]->aires = $parcelle->getIsInAires();
        }
        return $parcelles;
    }

    public function resetParcellesWithParcellesIds(array $parcellesIds)
    {
        if ($this->isAuditValide()) { return; }
        $this->remove('parcelles');
        $this->add('parcelles');
        if ($parcellesIds) {
            $parcelles = $this->getParcellaire()->getParcelles();
            foreach ($parcellesIds as $index => $pId) {
                if ($parcelles->exist($pId)) {
                    $parcelle = $this->parcelles->add($pId, $parcelles->get($pId));
                    if (is_null($parcelle->position)) {
                        $parcelle->position = $index;
                    }
                    if ( $parcelle->position != $index ) {
                        throw new sfException("La position (".$parcelle->position.") de la parcelle controlée est différent de son index ($index) dans le tableau parcelles");
                    }
                    if ($parcelle->needsUpdateNoeudControle()) {
                        $parcelle->updateNoeudControle();
                    }
                }
            }
        }
    }

    public function hasParcelle($parcelleId)
    {
        return $this->parcelles->exist($parcelleId);
    }

    protected function doSave() {
		$this->piece_document->generatePieces();
	}

    public function save()
    {
        $this->storeDeclarant();
        $this->generateMouvementsStatuts();
        return parent::save();
    }

    public function getParcelles() {
        return $this->_get('parcelles');
    }

    public function getStatutComputed()
    {
        if ($this->isControleCloture()) {
            return ControleClient::CONTROLE_STATUT_CONTROLE_CLOTURE;
        }
        if ($this->isTourneeTerminee()) {
            return ControleClient::CONTROLE_STATUT_TOURNEE_TERMINEE_AVEC_MANQUEMENTS_A_TRAITER;
        }
        if($this->isANotifier() && $this->isAuditValide()) {
            return ControleClient::CONTROLE_STATUT_A_NOTIFIER;
        }
        if ($this->isOrganise()) {
            return ControleClient::CONTROLE_STATUT_ORGANISE;
        }
        if ($this->isPlanifie()) {
            return ControleClient::CONTROLE_STATUT_A_ORGANISER;
        }
        return ControleClient::CONTROLE_STATUT_A_PLANIFIER;
    }

    public function isPlanifie()
    {
        return ($this->date_tournee);
    }

    public function isOrganise()
    {
        return $this->isPlanifie() && (count($this->parcelles));
    }

    public function isControle()
    {
        return $this->isOrganise() && count($this->manquements);
    }

    public function isTourneeTerminee()
    {
        return $this->isNotifiee();
    }

    public function isNotifiee()
    {
        return ($this->notification_date);
    }

    public function isANotifier()
    {
        return $this->needConstatsToBeCreated() || ! $this->isNotifiee();
    }

    public function hasManquements()
    {
        return $this->manquements && (count($this->manquements) > 0);
    }

    public function isAuditValide()
    {
        return $this->audit && $this->audit->saisie;
    }

    public function isControleCloture()
    {
        if (!$this->date_tournee || ! count($this->parcelles)) {
            return false;
        }
        if (! $this->hasManquements() && $this->notification_date ) {
            return true;
        }
        if (!$this->notification_date) {
            return false;
        }
        return ! $this->hasManquementNonCloture();
    }

    public function needConstatsToBeCreated()
    {
        $constats_id = [];
        foreach($this->parcelles as $pid => $p) {
            foreach ($p->controle->points as $key => $p) {
                foreach ($p->constats as $rtm => $constat) {
                    if ($constat->non_conforme) {
                        $constats_id[$rtm] = $rtm;
                    }
                }
            }
        }
        foreach(array_keys($constats_id) as $rtm) {
            if (!isset($this->manquements[$rtm])) {
                return true;
            }
        }
        return false;
    }

    public function generateMouvementsStatuts()
    {
        if ($this->exist('mouvements_statuts')) {
            $this->remove('mouvements_statuts');
        }
        $this->add('mouvements_statuts');
        $this->mouvements_statuts->add(null,  ['CONTROLE', $this->getDocumentDefinitionModel(), $this->getStatutComputed(), $this->identifiant] );
    }

    public function getGeoJson() {
        if ( ! $this->getParcellaire() ) {
            return [];
        }
        $geojson = $this->getParcellaire()->getGeoJson();
        $features = [];
        $parcelles = array_keys($this->getParcellaireParcelles());
        foreach ($geojson->features as $feature) {
            $tmp = $feature;
            foreach ($feature->properties->parcellaires as $i => $parcelle) {
                if (!in_array($parcelle->parcelle_id, $parcelles)) {
                    unset($tmp->properties->parcellaires[$i]);
                }
            }
            if (count($tmp->properties->parcellaires)) {
                $features[] = $tmp;
            }
        }
        $geojson->features = $features;
        return $geojson;
    }

    private $to_dump = false;
    public function isDump() {
        return $this->to_dump;
    }
    public function getDataToDump() {
        $this->to_dump = true;
        $d = $this->getData();
        $d->parcellaire_geojson = $this->getGeoJson();
        $d->parcellaire_parcelles = $this->getParcellaireParcelles();
        $d->agent_libelle = $this->getAgent()->getNomAAfficher();
        $d->validation = false;
        $d->revision = $this->_rev;
        $d->audit->needs_to_be_saved = false;
        $this->to_dump = false;
        return $d;
    }

    public function logDifferenceRevision($revApp, $idParcelle, $element)
    {
        $message = date("Y-m-d H:i:s")." : Document ". $this->_id ." - revisionApp : [".$revApp."] - revisionDocument : [".$this->_rev."] ";
        if ($idParcelle) {
            $message .= "pour parcelle [".$idParcelle."] = ";
        } else {
            $message .= "pour audit = ";
        }
        $message .= $element;
        error_log($message . "\n");
    }

    public function updateControle($idParcelle, $element)
    {
        unset($element['needs_to_be_saved']);
        if (!$idParcelle) {
            $this->audit = $element;
            return;
        }

        if (!$this->hasParcelle($idParcelle)) {
            $parcelleData = $this->getParcellaire()->getParcelleFromParcellaireId($idParcelle);
            if (!$parcelleData) {
                return;
            }
            $this->parcelles->add($idParcelle, $parcelleData);
        }

        $this->parcelles[$idParcelle]['controle'] = $element;
    }

    public function hasConstatTerrainActif()
    {
        foreach ($this->parcelles as $parcelleId => $parcelle) {
            foreach ($parcelle->controle->points as $pointId => $point) {
                foreach($point->constats as $constat) {
                    if ($constat->non_conforme == true) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    public function getListeManquements($fromConstatsActif = false)
    {
        $retManquements = array();
        foreach ($this->parcelles as $parcelleId => $parcelle) {
            foreach ($parcelle->controle->points as $pointId => $dataPoint) {
                foreach ($dataPoint->constats as $constatId => $constat) {
                    if ($this->manquements->exist($constatId) && ($this->manquements->$constatId->observations && $this->manquements->$constatId->parcelles_id)) {
                        $retManquements[$constatId] = $this->manquements[$constatId];
                        continue;
                    }
                    if ($dataPoint->conformite == null) {continue;}
                    if ($fromConstatsActif == true) {
                        if ($constat->non_conforme == false) {continue;}
                    }
                    if(!isset($retManquements[$constatId]) || !$retManquements[$constatId]) {
                        $retManquements[$constatId] = ControleManquement::freeInstance($this);
                        $retManquements[$constatId]->observations = '';
                        $retManquements[$constatId]->parcelles_id = [];
                    }
                    if (!isset($retManquements[$constatId]->libelle_point_de_controle) || !$retManquements[$constatId]->libelle_point_de_controle) {
                        $retManquements[$constatId]->libelle_point_de_controle = ControleConfiguration::getInstance()->getLibellePointDeControle($pointId);
                    }
                    if (!isset($retManquements[$constatId]->libelle_manquement) || !$retManquements[$constatId]->libelle_manquement) {
                        $retManquements[$constatId]->libelle_manquement = ControleConfiguration::getInstance()->getLibelleConstatWithPointId($constatId, $pointId);
                    }
                    $retManquements[$constatId]->parcelles_id->add(null, $parcelleId);
                    $retManquements[$constatId]->delais = ControleConfiguration::getInstance()->getDelaisConstat($constatId);
                    $retManquements[$constatId]->constat_date = $this->date_tournee;
                    $retManquements[$constatId]->actif = false;
                    $retManquements[$constatId]->observations .= $parcelleId . ' - ' . $constat->observations . "\n";
                }
            }
        }
        foreach ($this->manquements as $constatId => $manquement) {
            if (isset($retManquements[$constatId])) {continue;}
            $retManquements[$constatId] = $manquement;
        }
        return $retManquements;
    }

    public function getInfosManquement($codeConstat)
    {
        return array('libelle_point_de_controle' => ControleConfiguration::getInstance()->getLibellePointDeControleFromCodeConstat($codeConstat), 'libelle_manquement' => ControleConfiguration::getInstance()->getLibelleConstat($codeConstat), 'actif' => true, 'constat_date' => $this->date_tournee);
    }

    public function getManquementParcellesIdListe($manquementId)
    {
        $parcelles_id_list = array();
        foreach ($this->manquements[$manquementId]->parcelles_id as $id) if ($id) {
            $parcelles_id_list[] = $id;
        }
        return $parcelles_id_list;
    }

    public function addManquementManuel($constatId, $parcellesId)
    {
        if (! $parcellesId) {
            $manquement = $this->getInfosManquement($constatId);
            if (! $this->manquements->exist($constatId)) {
                $this->manquements->add($constatId, $manquement);
            }
        }
        else {
            foreach ($parcellesId as $parcelleId) {
                if ($this->manquements->exist($constatId) && in_array($parcelleId, $this->getManquementParcellesIdListe($constatId))) {return ;}
                $manquement = $this->getInfosManquement($constatId);
                if (! $this->manquements->exist($constatId)) {
                    $this->manquements->add($constatId, $manquement);
                }
                $this->manquements->get($constatId)->parcelles_id->add(null, $parcelleId);
            }
        }
    }

    public function addManquementTerrain($manquementId, $dataManquement)
    {
        if ($this->manquements->exist($manquementId)) {return ;}
        $this->manquements->add($manquementId, $dataManquement);
        $this->manquements->$manquementId->actif = true;
    }

    public function hasManquementTerrain()
    {
        foreach ($this->manquements as $manquementId => $manquement) {
            if (ControleConfiguration::getInstance()->isTerrain($manquementId)) {
                return true;
            }
        }
        return false;
    }

    public function generateManquements()
    {
        foreach ($this->getListeManquements(true) as $manquementId => $dataManquement) {
            $this->addManquementTerrain($manquementId, $dataManquement);
        }
    }

    public function getManquementsListe()
    {
        return $this->manquements;
    }

    public function getActiviteClient()
    {
        return HabilitationClient::getInstance()->findPreviousByIdentifiantAndDate($this->identifiant, $this->date)->getActivitesHabilites();
    }

    public function getManquementsActif()
    {
        $ret = array();
        foreach ($this->getManquementsListe() as $manquementId => $manquement) {
            if ($manquement->actif == true) {
                $ret[$manquementId] = $manquement;
            }
        }
        return $ret;
    }

    public function hasManquementNonCloture()
    {
        foreach($this->getManquementsActif() as $m) {
            if (! $m->cloture_date) {
                return true;
            }
        }
        return false;
    }

    public function hasManquementsActif()
    {
        if ($this->exist('manquements') && count($this->getManquementsActif())) {
            return true;
        }
        return false;
    }

    public function hasObservationOperateur()
    {
        if ($this->audit->exist('operateur_observation')) {
            return true;
        }
        return false;
    }

    public function hasObservationAgent()
    {
        if ($this->audit->exist('agent_observation')) {
            return true;
        }
        return false;
    }

    public function getProduitsHash()
    {
        $produitsHash = array();
        foreach ($this->parcelles as $parcelle) {
            $produitsHash[] = $parcelle->produit_hash;
        }
        return $produitsHash;
    }

    public function getObservationAgent()
    {
        if (! $this->hasObservationAgent()) {
            return '';
        }
        return $this->audit->agent_observation;
    }

    public function getObservationOperateur()
    {
        if (! $this->hasObservationOperateur()) {
            return '';
        }
        return $this->audit->operateur_observation;
    }

    public function initPotentielProductionProduits()
    {
        if  ( ! $this->getParcellaire() ) {
            return ;
        }
        $potentiel = PotentielProduction::retrievePotentielProductionFromParcellaire($this->getParcellaire());
        foreach ($potentiel->getProduits() as $ppproduit) {
            $this->surface_de_production->add($ppproduit->getLibelle(), $ppproduit->getSuperficieMax());
        }
    }

    public function getSuperficieTotale()
    {
        if ( ! $this->getParcellaire() ) {
            return 0;
        }
        return round($this->getParcellaire()->getSuperficieTotale(), 3);
    }

    public function getObservationsFromManquement($manquementId)
    {
        return $this->manquements[$manquementId]->observations;
    }

    public function getAgent()
    {
        return CompteClient::getInstance()->find($this->agent_identifiant);
    }

    public function getSortedManquementsActif()
    {
        $sorted_manquements = $this->getManquementsActif();
        ksort($sorted_manquements);
        return $sorted_manquements;
    }

    public function hasNotificationDate()
    {
        return $this->exist('notification_date') && $this->notification_date;
    }

    public function setNotificationDateControleEtManquements($date)
    {
        $this->notification_date = $date;
        foreach ($this->manquements as $manquement) {
            $manquement->notification_date = $date;
        }
    }

    public function getDateFormat($format = 'Y-m-d') {
        if (!$this->date) {
            return date($format);
        }
        return date($format, strtotime($this->date));
    }

    public function isPapier() {

        return $this->exist('papier') && $this->get('papier');
    }

    /**** PIECES ****/

    public function getAllPieces() {
        $pieces = array();

        if ($this->hasNotificationDate()) {
            $pieces[] = ['identifiant' => $this->identifiant, 'date_depot' => date('Y-m-d',  strtotime($this->notification_date)), 'libelle' => 'Contrôle du '.date('d/m/Y',  strtotime($this->date_tournee)), 'mime' => Piece::MIME_PDF, 'visibilite' => 1,'source' => $this->_id];
        }

        return $pieces;
    }

    public function generatePieces() {
    	return $this->piece_document->generatePieces();
    }

    public function generateUrlPiece($source = null) {
    	return sfContext::getInstance()->getRouting()->generate('controle_pdf', array('id' => $this->_id));
    }

    public static function getUrlVisualisationPiece($id, $admin = false) {
    	return sfContext::getInstance()->getRouting()->generate('controle_liste_manquements_operateur', array('id_controle' => $id));
    }

    public static function getUrlGenerationCsvPiece($id, $admin = false) {
    	return null;
    }

    public static function isVisualisationMasterUrl($admin = false) {
    	return true;
    }

    public static function isPieceEditable($admin = false) {
    	return false;
    }

    public function getCategorie(){
      return strtolower($this->type);
    }

    /**** FIN DES PIECES ****/


    public function getInfoPdf($controleIdentifiant, $parcelleId)
    {
        if (!$parcelleId) {
            throw new sfException('wrong parcelleId ('.$parcelleId.')');
        }
        $parcellaire = ParcellaireClient::getInstance()->getLast($controleIdentifiant);
        if (!$parcellaire) {
            throw new sfException('pas de parcellaire trouvé pour '.$controleIdentifiant);
        }
        $parcelle = $parcellaire->parcelles->get($parcelleId);
        if (!$parcelle) {
            throw new sfException('pas de parcelll trouvée pour '.$controleIdentifiant.'/'.$parcelleId);
        }
        return 'Parcelle ' . $parcelle->commune . ' - ' . $parcelle->section . $parcelle->numero_parcelle . ' - ' . $parcelle->cepage . ' - ' . $parcelle->campagne_plantation . ' - ' . $parcelle->superficie . ' ha';
    }

    public function updateParcellesNoeudControleIfNeeded()
    {
        foreach ($this->parcelles as $parcelle) {
            if ($parcelle->needsUpdateNoeudControle()) {
                $parcelle->updateNoeudControle();
            }
        }
    }
}
