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
                            $point->manquements->add($rtmKey, ['libelle' => $rtmConf['libelle'], 'conformite' => false, 'observations' => null]);
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
        $this->to_dump = false;
        return $d;
    }

    public function updateParcellePointsControleFromJson($json)
    {
        $retControleByParcelle = array();
        foreach ($json['controle']['parcelles'] as $parcelle) {
            // Je met le noeud controle du Json puis j'unset le sous-noeud "points" car c'est la seule update a faire
            $retControleByParcelle[$parcelle['parcelle_id']] = $parcelle['controle'];
            unset($retControleByParcelle[$parcelle['parcelle_id']]['points']);
            foreach ($parcelle['controle']['points'] as $nomPointDeControle => $dataPointDeControle) {
                if ($dataPointDeControle['conformite'] != 'NC') {
                    continue;
                }
                // Unset pour ne prendre que les manquements qui sont non conformes
                $retControleByParcelle[$parcelle['parcelle_id']]['points'][$nomPointDeControle] = $dataPointDeControle;
                unset($retControleByParcelle[$parcelle['parcelle_id']]['points'][$nomPointDeControle]['manquements']);
                foreach ($dataPointDeControle['manquements'] as $numRtm => $dataManquement) {
                    if ($dataManquement['conformite'] != 1) {
                        continue;
                    }
                    $retControleByParcelle[$parcelle['parcelle_id']]['points'][$nomPointDeControle]['manquements'][$numRtm] = $dataManquement;
                }
            }
        }
        foreach ($this->parcelles as $parcelleId => $dataParcelle) {
            $dataParcelle->controle = $retControleByParcelle[$parcelleId];
        }
        $this->save();
    }

    public function getListeManquements()
    {
        $retManquements = array();
        foreach ($this->parcelles as $parcelleId => $parcelle) {
            foreach ($parcelle->controle->points as $pointId => $dataPoint) {
                foreach ($dataPoint->manquements as $rtmId => $dataManquement) {
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

    public function getInfosManquement($rtmId)
    {
        return array('libelle_point_de_controle' => ControleConfiguration::getInstance()->getLibellePointDeControleFromCodeRtm($rtmId), 'libelle_manquement' => ControleConfiguration::getInstance()->getLibelleManquement($rtmId));
    }

    public function addManquement($rtmId)
    {
        if ($this->manquements->exist($rtmId)) {return ;}
        $manquement = $this->getInfosManquement($rtmId);
        $this->manquements->add($rtmId, $manquement);
        $this->save();
    }
}
