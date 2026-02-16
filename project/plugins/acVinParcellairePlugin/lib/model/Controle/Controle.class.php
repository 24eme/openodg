<?php
class Controle extends BaseControle
{
    protected $config = null;
    protected $parcellaire = null;
    protected $declarant_document = null;

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
    }

    public function initDoc($identifiant, $date, $type = ControleClient::TYPE_COUCHDB)
    {
        $this->identifiant = $identifiant;
        $this->date = $date;
        $this->campagne = ConfigurationClient::getInstance()->buildCampagne($date);
        $this->set('_id', ControleClient::TYPE_COUCHDB."-".$identifiant."-".str_replace('-', '', $date));
        $this->initDocuments();
        $this->storeDeclarant();
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
        foreach ($parcellaire->getParcelles() as $key => $parcelle) {
            $parcelles[$key] = $parcelle->getData();
            $parcelles[$key]->hasProblemExpirationCepage = $parcelle->hasProblemExpirationCepage();
            $parcelles[$key]->hasProblemEcartPieds = $parcelle->hasProblemEcartPieds();
            $parcelles[$key]->hasProblemCepageAutorise = $parcelle->hasProblemExpirationCepage();
            $parcelles[$key]->hasJeunesVignes = !$parcelle->hasJeunesVignes() && ParcellaireConfiguration::getInstance()->isJeunesVignesEnabled();
            $parcelles[$key]->isRealProduit = $parcelle->isRealProduit() && ParcellaireConfiguration::getInstance()->hasShowFilterProduitsConfiguration();
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
                        foreach ($pointConf['rtm'] as $rtmKey => $rtmConf) {
                            $point->constats->add($rtmKey, ['libelle' => $rtmConf['libelle'], 'conformite' => false, 'observations' => null]);
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

    protected function doSave()
    {
        return;
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
        if(!$this->date_tournee) {
            return ControleClient::CONTROLE_STATUT_A_PLANIFIER;
        }
        if (count($this->manquements)) {
            return ControleClient::CONTROLE_STATUT_EN_MANQUEMENT;
        }
        if (count($this->parcelles)) {
            return ControleClient::CONTROLE_STATUT_PLANIFIE;
        }
        if($this->date_tournee) {
            return ControleClient::CONTROLE_STATUT_A_ORGANISER;
        }
        return ControleClient::CONTROLE_STATUT_A_PLANIFIER;

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
        return $this->getParcellaire()->getGeoJson();
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
        $d->validation = false;
        $d->ppp = $this->getPotentielProductionProduits();
        $d->surface_production = round($this->getParcellaire()->getSuperficieTotale(), 3);
        $this->to_dump = false;
        return $d;
    }

    public function updateParcellePointsControleFromJson($json)
    {
        $retControleByParcelle = array();
        foreach ($json['controle']['parcelles'] as $parcelle) {
            $this->audit = $json['controle']['audit'];
            // Je met le noeud controle du Json puis j'unset le sous-noeud "points" car c'est la seule update a faire
            $retControleByParcelle[$parcelle['parcelle_id']] = $parcelle['controle'];
            unset($retControleByParcelle[$parcelle['parcelle_id']]['points']);
            foreach ($parcelle['controle']['points'] as $nomPointDeControle => $dataPointDeControle) {
                if ($dataPointDeControle['conformite'] != 'NC') {
                    continue;
                }
                // Unset pour ne prendre que les manquements qui sont non conformes
                $retControleByParcelle[$parcelle['parcelle_id']]['points'][$nomPointDeControle] = $dataPointDeControle;
                unset($retControleByParcelle[$parcelle['parcelle_id']]['points'][$nomPointDeControle]['constats']);
                foreach ($dataPointDeControle['constats'] as $numRtm => $dataManquement) {
                    if ($dataManquement['conformite'] != 1) {
                        continue;
                    }
                    $retControleByParcelle[$parcelle['parcelle_id']]['points'][$nomPointDeControle]['constats'][$numRtm] = $dataManquement;
                }
            }
        }
        foreach ($this->parcelles as $parcelleId => $dataParcelle) {
            $dataParcelle->controle = $retControleByParcelle[$parcelleId];
        }
        $this->save();
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

    public function getListeManquements()
    {
        $retManquements = array();
        foreach ($this->parcelles as $parcelleId => $parcelle) {
            foreach ($parcelle->controle->points as $pointId => $dataPoint) {
                foreach ($dataPoint->constats as $rtmId => $dataManquement) {
                    if ($this->manquements->exist($rtmId) && ($this->manquements->$rtmId->observations && $this->manquements->$rtmId->parcelles_id)) {
                        $retManquements[$rtmId] = $this->manquements[$rtmId];
                        continue;
                    }
                    if ($dataPoint->conformite == null) {continue;}
                    if(!isset($retManquements[$rtmId]) || !$retManquements[$rtmId]) {
                        $retManquements[$rtmId] = ControleManquement::freeInstance($this);
                        $retManquements[$rtmId]->observations = '';
                        $retManquements[$rtmId]->parcelles_id = [];
                    }
                    if (!isset($retManquements[$rtmId]->libelle_point_de_controle) || !$retManquements[$rtmId]->libelle_point_de_controle) {
                        $retManquements[$rtmId]->libelle_point_de_controle = ControleConfiguration::getInstance()->getLibellePointDeControle($pointId);
                    }
                    if (!isset($retManquements[$rtmId]->libelle_manquement) || !$retManquements[$rtmId]->libelle_manquement) {
                        $retManquements[$rtmId]->libelle_manquement = ControleConfiguration::getInstance()->getLibelleManquementWithPointId($rtmId, $pointId);
                    }
                    $retManquements[$rtmId]->parcelles_id->add(null, $parcelleId);
                    $retManquements[$rtmId]->delais = ControleConfiguration::getInstance()->getDelaisManquement($pointId, $rtmId);
                    $retManquements[$rtmId]->constat_date = $this->date_tournee;
                    $retManquements[$rtmId]->actif = false;
                    $retManquements[$rtmId]->observations .= $parcelleId . ' - ' . $dataManquement->observations . "\n";
                }
            }
        }
        foreach ($this->manquements as $rtmId => $manquement) {
            if (isset($retManquements[$rtmId])) {continue;}
            $retManquements[$rtmId] = $manquement;
        }
        return $retManquements;
    }

    public function getInfosManquement($rtmId, $parcelleId)
    {
        return array('libelle_point_de_controle' => ControleConfiguration::getInstance()->getLibellePointDeControleFromCodeRtm($rtmId), 'libelle_manquement' => ControleConfiguration::getInstance()->getLibelleManquement($rtmId), 'actif' => true, 'constat_date' => $this->date_tournee, 'parcelles_id' => [$parcelleId]);
    }

    public function addManquementDocumentaire($rtmId, $parcelleId)
    {
        if ($this->manquements->exist($rtmId)) {return ;}
        $manquement = $this->getInfosManquement($rtmId, $parcelleId);
        $this->manquements->add($rtmId, $manquement);
    }

    public function addManquementTerrain($rtmId, $dataManquement)
    {
        if ($this->manquements->exist($rtmId)) {return ;}
        $this->manquements->add($rtmId, $dataManquement);
        $this->manquements->$rtmId->actif = true;
    }

    public function hasManquementTerrain()
    {
        foreach ($this->manquements as $rtmId => $manquement) {
            if ($manquement->parcelles_id) {
                return true;
            }
        }
        return false;
    }

    public function deleteManquement($rtmId)
    {
        if ($this->manquements->exist($rtmId)) {
            $this->manquement->remove($rtmId);
        }
    }

    public function generateManquements()
    {
        foreach ($this->getListeManquements() as $rtmId => $dataManquement) {
            $this->addManquementTerrain($rtmId, $dataManquement);
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
        foreach ($this->getManquementsListe() as $rtmId => $manquement) {
            if ($manquement->actif == true) {
                $ret[$rtmId] = $manquement;
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

    public function getPotentielProductionProduits()
    {
        $potentiel = PotentielProduction::retrievePotentielProductionFromParcellaire($this->parcellaire);
        $ppproduits = array();
        foreach ($potentiel->getProduits() as $ppproduit) {
            $ppproduits[$ppproduit->getLibelle()] = $ppproduit->getSuperficieMax();
        }
        return $ppproduits;
    }
}
