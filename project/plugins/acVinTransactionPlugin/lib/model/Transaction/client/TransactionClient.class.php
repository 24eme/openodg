<?php

class ConditionnementClient extends acCouchdbClient {

    const TYPE_MODEL = "Conditionnement";
    const TYPE_COUCHDB = "CONDITIONNEMENT";

    public static function getInstance()
    {
        return acCouchdbManager::getClient("Conditionnement");
    }

    public function find($id, $hydrate = self::HYDRATE_DOCUMENT, $force_return_ls = false) {
        $doc = parent::find($id, $hydrate, $force_return_ls);

        if($doc && $doc->type != self::TYPE_MODEL) {

            throw new sfException(sprintf("Document \"%s\" is not type of \"%s\"", $id, self::TYPE_MODEL));
        }

        return $doc;
    }

    public function findByIdentifiantAndDate($identifiant, $date, $hydrate = acCouchdbClient::HYDRATE_DOCUMENT) {
        $docid = self::TYPE_COUCHDB.'-'.$identifiant.'-'.str_replace('-', '', $date);
        $doc = $this->find($docid);
        return $doc;
    }


    public function findByIdentifiantAndDateOrCreateIt($identifiant, $date, $hydrate = acCouchdbClient::HYDRATE_DOCUMENT) {
        $doc = $this->findByIdentifiantAndDate($identifiant, $date, $hydrate);
        if (!$doc) {
            $doc = $this->createDoc($identifiant, $date);
        }
        return $doc;
    }

    public function createDoc($identifiant, $campagne, $date = null, $papier = false)
    {
        $doc = new Conditionnement();
        $doc->initDoc($identifiant, $campagne, $date);

        $doc->storeDeclarant();

        $etablissement = $doc->getEtablissementObject();

        if(!$etablissement->hasFamille(EtablissementFamilles::FAMILLE_PRODUCTEUR)) {
            $doc->add('non_recoltant', 1);
        }

        if(!$etablissement->hasFamille(EtablissementFamilles::FAMILLE_CONDITIONNEUR)) {
            $doc->add('non_conditionneur', 1);
        }

        if($papier) {
            $doc->add('papier', 1);
        }

        return $doc;
    }

    public function getIds($periode) {
        $ids = $this->startkey_docid(sprintf("CONDITIONNEMENT-%s-%s", "0000000000", "00000000"))
                    ->endkey_docid(sprintf("CONDITIONNEMENT-%s-%s", "ZZZZZZZZZZ", "99999999"))
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
        $dates = sfConfig::get('app_dates_ouverture_conditionnement');

        return $dates['debut'];
    }

    public function getDateOuvertureFin() {
        $dates = sfConfig::get('app_dates_ouverture_conditionnement');

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

        return $this->startkey(sprintf("CONDITIONNEMENT-%s-%s", $identifiant, $campagne_from))
                    ->endkey(sprintf("CONDITIONNEMENT-%s-%s_ZZZZZZZZZZZZZZ", $identifiant, $campagne_to))
                    ->execute($hydrate);
    }

    public function findConditionnementsByCampagne($identifiant, $campagne, $hydrate = acCouchdbClient::HYDRATE_DOCUMENT){
      $allConditionnement = ConditionnementClient::getInstance()->getHistory($identifiant);
      $conditionnements = array();
      foreach ($allConditionnement as $key => $conditionnement) {
        if($conditionnement->campagne == $campagne){
          $conditionnements[] = $conditionnement;
        }
      }
      return $conditionnements;
    }

}
