<?php

class DRevClient extends acCouchdbClient implements FacturableClient {

    const TYPE_MODEL = "DRev";
    const TYPE_COUCHDB = "DREV";

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
        $identifiant = str_replace("E", "", $identifiant);
        $campagne = $campagne."";
        $drevs = array();
        $ids = DeclarationClient::getInstance()->viewByIdentifiantCampagneAndType($identifiant, $campagne, self::TYPE_MODEL);
        ksort($ids);
        $idBase = "DREV-".$identifiant."-".$campagne;
        if(!array_key_exists("DREV-".$identifiant."-".$campagne, $ids)) {
            $ids[$idBase] = $idBase;
        }
        foreach ($ids as $id => $value) {
            $drev = $this->find($id);
            if(!$drev->validation_odg) {

                continue;
            }
            $drevs[$drev->_id] = $drev;
        }

        return $drevs;
    }

    public function createDoc($identifiant, $campagne, $papier = false)
    {
        $drev = new DRev();
        $drev->initDoc($identifiant, $campagne);

        $etablissement = $drev->getEtablissementObject();

        if(!$etablissement->hasFamille(EtablissementClient::FAMILLE_PRODUCTEUR)) {
            $drev->add('non_recoltant', 1);
        }

        if(!$etablissement->hasFamille(EtablissementClient::FAMILLE_VINIFICATEUR)) {
            $drev->add('non_vinificateur', 1);
        }

        if(!$etablissement->hasFamille(EtablissementClient::FAMILLE_CONDITIONNEUR)) {
            $drev->add('non_conditionneur', 1);
        }

        if($papier) {
            $drev->add('papier', 1);
        }

        $drev_previous = $this->find(sprintf("DREV-%s-%s", $identifiant, ConfigurationClient::getInstance()->getCampagneManager()->getPrevious($campagne)));

        if($drev_previous) {
            $drev->updateFromDRev($drev_previous);
        }

        if(count($drev->declaration->getAppellations()) == 0 && $drev->isNonRecoltant()) {
            $drev->initAppellations();
        }

        $drev->populateVCIFromRegistre();

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
        $campagne_to = ConfigurationClient::getInstance()->getCampagneManager()->getCurrent()."";

        return $this->startkey(sprintf("DREV-%s-%s", $identifiant, $campagne_from))
                    ->endkey(sprintf("DREV-%s-%s_ZZZZZZZZZZZZZZ", $identifiant, $campagne_to))
                    ->execute($hydrate);
    }

    public function getOrdrePrelevements() {
        return array("cuve" => array("cuve_ALSACE", "cuve_GRDCRU", "cuve_CREMANT", "cuve_VTSGN"), "bouteille" => array("bouteille_ALSACE","bouteille_GRDCRU","bouteille_VTSGN"));
    }
}
