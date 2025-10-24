<?php
class Controle extends BaseControle
{
    protected $config = null;
    protected $parcellaire = null;

    public function getConfig()
    {
        if (!$this->config) {
            $this->config = ControleConfiguration::getInstance();
        }
        return $this->config;
    }

    protected function initDocuments()
    {
        $this->declarant_document = new DeclarantDocument($this);
    }

    public function initDoc($identifiant, $date, $type = ControleClient::TYPE_COUCHDB)
    {
        $this->identifiant = $identifiant;
        $this->date = $date;
        $this->campagne = ConfigurationClient::getInstance()->buildCampagne($date);
        $this->set('_id', ControleClient::TYPE_COUCHDB."-".$identifiant."-".str_replace('-', '', $date));
        $this->storeDeclarant();
    }

    public function storeDeclarant() {
        parent::storeDeclarant();
        $etablissement = $this->getEtablissementObject();
        if($etablissement->exist('secteur')) {
            $this->document->secteur = $etablissement->secteur;
        }
        foreach($etablissement->liaisons_operateurs as $liaison) {
            if($liaison->type_liaison == EtablissementClient::TYPE_LIAISON_COOPERATIVE) {
                $this->liaisons_operateurs->add($liaison->getKey(), $liaison);
            }
        }
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

    public function updateParcelles(array $parcellesIds)
    {
        $this->remove('parcelles');
        $this->add('parcelles');
        if ($parcellesIds) {
            $parcelles = $this->getParcellaire()->getParcelles();
            foreach ($parcellesIds as $pId) {
                if ($parcelles->exist($pId)) {
                    $this->setPointsControle($this->parcelles->add($pId, $parcelles->get($pId)));
                }
            }
        }
    }

    public function setPointsControle($parcelle)
    {
        $conf = $this->getConfig();
        $points = $conf->getFromConfig('points') ?? [];
        foreach ($points as $point) {
            $parcelle->controle->points->add($point, null);
        }
        return $parcelle;
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

    public function getStatutComputed()
    {
        if($this->date_tournee) {
            return ControleClient::CONTROLE_STATUT_PLANIFIE;
        }
        if(count($this->parcelles)) {
            return ControleClient::CONTROLE_STATUT_A_PLANIFIER;
        }

        return ControleClient::CONTROLE_STATUT_A_ORGANISER;

    }

    public function generateMouvementsStatuts()
    {
        if ($this->exist('mouvements_statuts')) {
            $this->remove('mouvements_statuts');
        }
        $this->add('mouvements_statuts');
        $this->mouvements_statuts->add(null,  ['CONTROLE', $this->getDocumentDefinitionModel(), $this->getStatutComputed(), $this->identifiant] );
        print_r(['generateMouvementsStatuts', $this->mouvements_statuts]);
    }

}
