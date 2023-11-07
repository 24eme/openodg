<?php

class PMCClient extends acCouchdbClient
{
    const TYPE_MODEL = "PMC";
    const TYPE_COUCHDB = "PMC";

    public static function getInstance()
    {
        return acCouchdbManager::getClient("PMC");
    }

    public function find($id, $hydrate = self::HYDRATE_DOCUMENT, $force_return_ls = false) {
        $doc = parent::find($id, $hydrate, $force_return_ls);

        if($doc && in_array($doc->type, [PMCClient::TYPE_MODEL, PMCNCClient::TYPE_MODEL]) === false) {

            throw new sfException(sprintf("Document \"%s\" is not type of \"%s\"", $id, self::TYPE_MODEL));
        }

        return $doc;
    }

    public function findBrouillon($identifiant, $periode = null)
    {
        if (!$periode) {
            $periode = ConfigurationClient::getInstance()->getCampagneVinicole(CampagneManager::FORMAT_PREMIERE_ANNEE)->getCurrent();
        }

        $docs = DeclarationTousView::getInstance()->getByTypeCampagneIdentifiant(get_called_class()::TYPE_MODEL, ConfigurationClient::getInstance()->buildCampagneFromYearOrCampagne($periode), $identifiant);

        foreach ($docs->rows as $doc) {
            if ($doc->key[DeclarationTousView::KEY_STATUT] == DeclarationTousView::STATUT_BROUILLON) {
                return $this->find($doc->id);
            }
        }
        return null;
    }

    public function findByIdentifiantAndDate($identifiant, $date, $hydrate = acCouchdbClient::HYDRATE_DOCUMENT) {
        $docid = get_called_class()::TYPE_COUCHDB.'-'.$identifiant.'-'.preg_replace('/[^0-9]/', '', $date);
        $doc = $this->find($docid);
        return $doc;
    }


    public function findByIdentifiantAndDateOrCreateIt($identifiant, $campagne, $date, $hydrate = acCouchdbClient::HYDRATE_DOCUMENT) {
        $doc = $this->findByIdentifiantAndDate($identifiant, $date);
        if (!$doc) {
            $doc = $this->createDoc($identifiant, $campagne, $date);
        }
        return $doc;
    }

    public function createDoc($identifiant, $periode, $date, $papier = false)
    {
        $doc = new PMC();
        $doc->initDoc($identifiant, $periode, $date);

        $doc->storeDeclarant();

        $etablissement = $doc->getEtablissementObject();

        if($papier) {
            $doc->add('papier', 1);
        }

        return $doc;
    }

    public function getIds($periode) {
        $ids = $this->startkey_docid(sprintf("%s-%s-%s", self::TYPE_MODEL, "0000000000", "00000000"))
                    ->endkey_docid(sprintf("%s-%s-%s", self::TYPE_MODEL, "ZZZZZZZZZZ", "99999999"))
                    ->execute(acCouchdbClient::HYDRATE_ON_DEMAND)->getIds();

        $ids_periode = array();

        foreach($ids as $id) {
            if(strpos($id, "-".$periode) !== false) {
                $ids_periode[] = $id;
            }
        }

        sort($ids_periode);

        return $ids_periode;
    }

    public function getDateOuvertureDebut() {
        $dates = sfConfig::get('app_dates_ouverture_pmc');

        return $dates['debut'];
    }

    public function getDateOuvertureFin() {
        $dates = sfConfig::get('app_dates_ouverture_pmc');

        return $dates['fin'];
    }

    public function isOpen($date = null) {
        if(is_null($date)) {

            $date = date('Y-m-d');
        }

        return $date >= $this->getDateOuvertureDebut() && $date <= $this->getDateOuvertureFin();
    }

    public function getHistory($identifiant, $hydrate = acCouchdbClient::HYDRATE_DOCUMENT) {
        $campagne_from = "00000000";
        $campagne_to = "99999999";

        return $this->startkey(sprintf("%s-%s-%s", self::TYPE_MODEL, $identifiant, $campagne_from))
                    ->endkey(sprintf("%s-%s-%s_ZZZZZZZZZZZZZZ", self::TYPE_MODEL, $identifiant, $campagne_to))
                    ->execute($hydrate);
    }

    public function findPMCsByCampagne($identifiant, $campagne, $hydrate = acCouchdbClient::HYDRATE_DOCUMENT){
      $allPMC = PMCClient::getInstance()->getHistory($identifiant);
      $pmcs = array();
      foreach ($allPMC as $key => $pmc) {
        if($pmc->campagne == $campagne){
          $pmcs[] = $pmc;
        }
      }
      return $pmcs;
    }

}
