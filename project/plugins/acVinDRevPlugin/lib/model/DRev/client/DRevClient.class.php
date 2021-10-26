<?php

class DRevClient extends acCouchdbClient implements FacturableClient {

    const TYPE_MODEL = "DRev";
    const TYPE_COUCHDB = "DREV";
    const DENOMINATION_BIO_TOTAL_DEPRECATED = "BIO_TOTAL";
    const DENOMINATION_BIO_PARTIEL_DEPRECATED = "BIO_PARTIEL";
    const DENOMINATION_BIO = "BIO";
    const DENOMINATION_HVE = "HVE";
    const DENOMINATION_CONVENTIONNEL = "CONVENTIONNEL";
    const DENOMINATION_BIO_LIBELLE_AUTO = "Agriculture Biologique";
    const DENOMINATION_HVE_LIBELLE_AUTO = "HVE";
    const LOT_DESTINATION_VRAC_FRANCE_ET_CONDITIONNEMENT = 'VRAC_FRANCE_ET_CONDITIONNEMENT';
    const LOT_DESTINATION_VRAC_FRANCE = 'VRAC_FRANCE';
    const LOT_DESTINATION_VRAC_EXPORT = 'VRAC_EXPORT';
    const LOT_DESTINATION_CONDITIONNEMENT = 'CONDITIONNEMENT';
    const LOT_DESTINATION_VRAC_FRANCE_ET_VRAC_EXPORT = "VRAC_FRANCE_ET_VRAC_EXPORT";
    const LOT_DESTINATION_VRAC_EXPORT_ET_CONDITIONNEMENT = "VRAC_EXPORT_ET_CONDITIONNEMENT";
    const LOT_DESTINATION_VRAC_EXPORT_VRAC_FRANCE_ET_CONDITIONNEMENT = "VRAC_EXPORT_VRAC_FRANCE_ET_CONDITIONNEMENT";

    const STATUT_EN_ATTENTE = "En attente";
    const STATUT_SIGNE = "À approuver";
    const STATUT_VALIDATION_ODG = "Approuvé";
    const STATUT_BROUILLON = null;

    public static $denominationsAuto = array(
        self::DENOMINATION_CONVENTIONNEL => "Conventionnel",
        self::DENOMINATION_HVE => "HVE",
        self::DENOMINATION_BIO => "Bio",
    );

    public static $lotDestinationsType = array(
        DRevClient::LOT_DESTINATION_CONDITIONNEMENT => "Conditionnement",
        DRevClient::LOT_DESTINATION_VRAC_FRANCE => "Vrac France",
        DRevClient::LOT_DESTINATION_VRAC_EXPORT => "Vrac Export",
        DRevClient::LOT_DESTINATION_VRAC_FRANCE_ET_CONDITIONNEMENT => "Vrac France et Conditionnement",
        DRevClient::LOT_DESTINATION_VRAC_FRANCE_ET_VRAC_EXPORT => "Vrac France et Vrac Export",
        DRevClient::LOT_DESTINATION_VRAC_EXPORT_ET_CONDITIONNEMENT => "Vrac Export et Conditionnement",
        DRevClient::LOT_DESTINATION_VRAC_EXPORT_VRAC_FRANCE_ET_CONDITIONNEMENT => "Vrac Export, Vrac France et Conditionnement"
    );

    public static function getInstance()
    {

        return acCouchdbManager::getClient("DRev");
    }

    public function find($id, $hydrate = self::HYDRATE_DOCUMENT, $force_return_ls = false) {
        $doc = parent::find($id, $hydrate, $force_return_ls);

        if($doc && $doc->type != self::TYPE_MODEL) {

            throw new sfException(sprintf("Document \"%s\" is not type of \"%s\"", $id, self::TYPE_MODEL));
        }

        return $doc;
    }

    public function findMasterByIdentifiantAndCampagne($identifiant, $campagne, $hydrate = acCouchdbClient::HYDRATE_DOCUMENT) {
        $drevs = DeclarationClient::getInstance()->viewByIdentifiantCampagneAndType($identifiant, $campagne, self::TYPE_MODEL);
        foreach ($drevs as $id => $drev) {

            return $this->find($id, $hydrate);
        }

        return null;
    }

    public function findFacturable($identifiant, $campagne) {
    	$drev = $this->find('DREV-'.str_replace("E", "", $identifiant).'-'.$campagne);

        if($drev && !$drev->validation_odg) {

            return null;
        }

        return $drev;
    }

    public function createDoc($identifiant, $campagne, $papier = false, $reprisePrecedente = true)
    {
        $drev = new DRev();
        $drev->initDoc($identifiant, $campagne);

        $drev->storeDeclarant();

        $etablissement = $drev->getEtablissementObject();

        if(!$etablissement->hasFamille(EtablissementFamilles::FAMILLE_PRODUCTEUR)) {
            $drev->add('non_recoltant', 1);
        }

        if(!$etablissement->hasFamille(EtablissementFamilles::FAMILLE_CONDITIONNEUR)) {
            $drev->add('non_conditionneur', 1);
        }

        if($papier) {
            $drev->add('papier', 1);
        }

        if($reprisePrecedente) {
            $previous_drev = self::findMasterByIdentifiantAndCampagne($identifiant, $campagne - 1 );
            if ($previous_drev) {
                $drev->set('chais', $previous_drev->chais->toArray(true, false));
              foreach($previous_drev->getProduitsVci() as $produit) {
                if ($produit->vci->stock_final) {
                  $drev->cloneProduit($produit);
                }
              }
            }
        }

        return $drev;
    }

    public function getIds($campagne) {
        $ids = $this->startkey_docid(sprintf("DREV-%s-%s", "0000000000", "0000"))
                    ->endkey_docid(sprintf("DREV-%s-%s", "9999999999", "9999"))
                    ->execute(acCouchdbClient::HYDRATE_ON_DEMAND)->getIds();

        $ids_campagne = array();

        foreach($ids as $id) {
            if(strpos($id, "-".$campagne) !== false) {
                $ids_campagne[] = $id;
            }
        }

        sort($ids_campagne);

        return $ids_campagne;
    }

    public function getDateOuvertureDebut() {
        $dates = sfConfig::get('app_dates_ouverture_drev');

        return $dates['debut'];
    }

    public function getDateOuvertureFin() {
        $dates = sfConfig::get('app_dates_ouverture_drev');

        return $dates['fin'];
    }

    public function isOpen($date = null) {
        if(is_null($date)) {

            $date = date('Y-m-d');
        }

        return $date >= $this->getDateOuvertureDebut() && $date <= $this->getDateOuvertureFin();
    }

    public function getHistory($identifiant, $hydrate = acCouchdbClient::HYDRATE_DOCUMENT) {
        $campagne_from = "0000";
        $campagne_to = "9999";

        return $this->startkey(sprintf("DREV-%s-%s", $identifiant, $campagne_from))
                    ->endkey(sprintf("DREV-%s-%s_ZZZZZZZZZZZZZZ", $identifiant, $campagne_to))
                    ->execute($hydrate);
    }

    public function getOrdrePrelevements() {
        return array("cuve" => array("cuve_ALSACE", "cuve_GRDCRU", "cuve_VTSGN"), "bouteille" => array("bouteille_ALSACE","bouteille_GRDCRU","bouteille_VTSGN"));
    }

    public function getNonHabilitationINAO($drev) {
        $non_habilite = array();
        $identifiant = $drev->declarant->cvi;
        if (!$identifiant) {
            $identifiant = preg_replace('/ /', '', $drev->declarant->siret);
        }
        if (!$identifiant) {
            return array();
        }
        $regions = DrevConfiguration::getInstance()->getOdgRegions();
        foreach($regions as $region) {
            $produits = $drev->getProduits($region);
            if (!count($produits)) {
                continue;
            }
            $inao_fichier = DrevConfiguration::getInstance()->getOdgINAOHabilitationFile($region);
            if (!$inao_fichier) {
                continue;
            }
            $inao_csv = new INAOHabilitationCsvFile(sfConfig::get('sf_root_dir').'/'.$inao_fichier);
            foreach ($produits as $produit) {
                if (! $inao_csv->isHabilite($identifiant, $produit->getConfig()->getAppellation()->getLibelle())) {
                    $non_habilite[] = $produit;
                }
            }
        }
        return $non_habilite;
    }

    public function getLastDrevFromEtablissement($etablissement){
      $lastDrevs = $this->getHistory($etablissement->getIdentifiant());
      foreach ($lastDrevs as $drev) {
        return $drev;
      }
      return null;
    }

}
