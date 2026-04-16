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
        return EtablissementClient::getInstance()->findByCvi($this->declarant->cvi)->getLiaisonsOfType(EtablissementFamilles::FAMILLE_COOPERATIVE, true);
    }

    public function getLibelleLiaison() {
        $libelles = [];
        foreach($this->liaisons_operateurs as $liaison) {
            $libelles[] = $liaison->libelle_etablissement;
        }
        return implode(', ', $libelles);
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

    public function updateParcelles(array $parcellesIds)
    {
        $this->remove('parcelles');
        $this->add('parcelles');
        if ($parcellesIds) {
            $parcelles = $this->getParcellaire()->getParcelles();
            foreach ($parcellesIds as $pId) {
                if ($parcelles->exist($pId)) {
                    $parcelle = $this->parcelles->add($pId, $parcelles->get($pId));
                    foreach (ControleConfiguration::getInstance()->getPointsDeControle() as $pointKey => $pointConf) {
                        $point = $parcelle->controle->points->add($pointKey);
                        $point->libelle = $pointConf['libelle'];
                        $hasConstat = false;
                        foreach ($pointConf['constats'] as $constatKey => $constatConf) {
                            if ($constatConf['terrain'] && in_array($this->type_tournee, $constatConf['types'])) {
                                $point->constats->add($constatKey, ['libelle' => $constatConf['libelle'], 'conformite' => false, 'observations' => null]);
                                $hasConstat = true;
                            }
                        }
                        if (! $hasConstat) {
                            $parcelle->controle->points->remove($pointKey);
                        }
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
        if(!$this->isPlanifie()) {
            return ControleClient::CONTROLE_STATUT_A_PLANIFIER;
        }
        if ($this->isControle()) {
            return ControleClient::CONTROLE_STATUT_EN_MANQUEMENT;
        }
        if ($this->isOrganise()) {
            return ControleClient::CONTROLE_STATUT_ORGANISE;
        }
        if ($this->isPlanifie()) {
            return ControleClient::CONTROLE_STATUT_A_ORGANISER;
        }
        if ($this->isTermine()) {
            return ControleClient::CONTROLE_STATUT_TERMINE;
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

    public function isTermine()
    {
        return $this->manquements_valides;
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

    public function hasConstatTerrain()
    {
        foreach ($this->parcelles as $parcelleId => $parcelle) {
            foreach ($parcelle->controle->points as $dataPoint) {
                if (! empty($dataPoint)) {
                    return true;
                }
            }
        }
        return false;
    }

    public function hasConstatTerrainActif()
    {
        foreach ($this->parcelles as $parcelleId => $parcelle) {
            foreach ($parcelle->controle->points as $pointId => $point) {
                foreach($point->constats as $constat) {
                    if ($constat->conformite == true) {
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
                foreach ($dataPoint->constats as $constatId => $dataManquement) {
                    if ($this->manquements->exist($constatId) && ($this->manquements->$constatId->observations && $this->manquements->$constatId->parcelles_id)) {
                        $retManquements[$constatId] = $this->manquements[$constatId];
                        continue;
                    }
                    if ($dataPoint->conformite == null) {continue;}
                    if ($fromConstatsActif == true) {
                        if ($dataManquement->conformite == false) {continue;}
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
                    $retManquements[$constatId]->observations .= $parcelleId . ' - ' . $dataManquement->observations . "\n";
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
        foreach ($this->manquements[$manquementId]->parcelles_id as $id) {
            $parcelles_id_list[] = $id;
        }
        return $parcelles_id_list;
    }

    public function addManquementManuel($manquementId, $parcelleId)
    {
        if ($this->manquements->exist($manquementId) && in_array($parcelleId, $this->getManquementParcellesIdListe($manquementId))) {return ;}
        $manquement = $this->getInfosManquement($manquementId);
        if (! $this->manquements->exist($manquementId)) {
            $this->manquements->add($manquementId, $manquement);
        }
        $this->manquements->get($manquementId)->parcelles_id->add(null, $parcelleId);
        $this->save();
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

    public function deleteManquement($manquementId)
    {
        if ($this->manquements->exist($manquementId)) {
            $this->manquement->remove($manquementId);
        }
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

    public function getDateFr()
    {
        preg_match('/([0-9]{4})([0-9]{2})([0-9]{2})/', $this->date, $matches);
        return $matches[3].'/'.$matches[2].'/'.$matches[1];
    }

    public function getDateEn()
    {
        preg_match('/([0-9]{4})([0-9]{2})([0-9]{2})/', $this->date, $matches);
        return $matches[1].'-'.$matches[2].'-'.$matches[3];
    }

    public function getActiviteClient()
    {
        return HabilitationClient::getInstance()->findPreviousByIdentifiantAndDate($this->identifiant, $this->getDateEn())->getActivitesHabilites();
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

}
