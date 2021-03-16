<?php
class MandatSepaClient extends acCouchdbClient {

  const FREQUENCE_PRELEVEMENT_RECURRENT = 'RECURRENT';
  const FREQUENCE_PRELEVEMENT_PONCTUEL = 'PONCTUEL';

  public static $frequence_prelevement_libelles = array(
      self::FREQUENCE_PRELEVEMENT_RECURRENT => "Récurrent",
      self::FREQUENCE_PRELEVEMENT_PONCTUEL => "Ponctuel"
  );

  public static function getInstance() {
      return acCouchdbManager::getClient("MandatSepa");
  }

  public function find($id, $hydrate = self::HYDRATE_DOCUMENT, $force_return_ls = false) {
      $doc = parent::find($id, $hydrate, $force_return_ls);
      if($doc && $doc->type != self::TYPE_MODEL) {
          throw new sfException(sprintf("Document \"%s\" is not type of \"%s\"", $id, self::TYPE_MODEL));
      }
      return $doc;
  }

  public function findLastBySociete($id_or_object) {
    if (is_object($id_or_object)) {
      $id_or_object = $id_or_object->getIdentifiant();
    }
    $ids = $this->startkey_docid(sprintf(self::TYPE_COUCHDB."-%s-%s", $id_or_object, "00000000"))
                ->endkey_docid(sprintf(self::TYPE_COUCHDB."-%s-%s", $id_or_object, "99999999"))
                ->execute(acCouchdbClient::HYDRATE_ON_DEMAND)->getIds();

    $nbIds = count($ids);
    if (!$nbIds) {
      return null;
    }
    return $this->find($ids[$nbIds-1]);
  }

  public function createDoc($debiteur, $creancier = null, $date = null, $frequence = null) {
      $mandatSepaConf = MandatSepaConfiguration::getInstance();
      $mandatSepa = new MandatSepa();
      $mandatSepa->setDebiteur($debiteur);
      if (!$creancier) {
        $creancier = MandatSepaConfiguration::getInstance();
      }
      $mandatSepa->setCreancier($creancier);
      if (!preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $date)) {
        $date = date('Y-m-d');
      }
      $mandatSepa->setDate($date);
      if (!in_array($frequence, array_keys(self::$frequence_prelevement_libelles))) {
        $frequence = $mandatSepaConf->getFrequencePrelevement();
        if (!$frequence) {
          $frequence = self::FREQUENCE_PRELEVEMENT_RECURRENT;
        }
      }
      $mandatSepa->setFrequencePrelevement($frequence);
      $mandatSepa->setMentionAutorisation($mandatSepaConf->getMentionAutorisation());
      $mandatSepa->setMentionRemboursement($mandatSepaConf->getMentionRemboursement());
      $mandatSepa->setMentionDroits($mandatSepaConf->getMentionDroits());
      $mandatSepa->setIsSigne(0);
      return $mandatSepa;
  }
}
