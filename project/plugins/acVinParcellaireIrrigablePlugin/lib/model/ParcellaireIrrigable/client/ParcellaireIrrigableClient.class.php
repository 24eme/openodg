<?php

class ParcellaireIrrigableClient extends acCouchdbClient {

      const TYPE_MODEL = "ParcellaireIrrigable";
      const TYPE_COUCHDB = "PARCELLAIREIRRIGABLE";



      public static function getInstance() {
          return acCouchdbManager::getClient("parcellaireIrrigable");
      }

      public function findOrCreate($identifiant, $campagne, $type = self::TYPE_COUCHDB) {
          if (strlen($campagne) != 4)
              throw new sfException("La campagne doit être une année et non " . $campagne);
          $parcellaireIrrigable = $this->find($this->buildId($identifiant, $campagne, $type));
          if (is_null($parcellaireIrrigable)) {
              $parcellaireIrrigable = $this->createDoc($identifiant, $campagne, $type);
          }

          return $parcellaireIrrigable;
      }


      public function buildId($identifiant, $campagne, $type = self::TYPE_COUCHDB) {
          $id = "$type-%s-%s";
          return sprintf($id, $identifiant, $campagne);
      }

      public function createDoc($identifiant, $campagne, $type = self::TYPE_COUCHDB) {
          $parcellaireIrrigable = new parcellaireIrrigable();
          $parcellaireIrrigable->initDoc($identifiant, $campagne, $type);

          return $parcellaireIrrigable;
      }

      public function getHistory($identifiant, $type = self::TYPE_COUCHDB, $hydrate = acCouchdbClient::HYDRATE_DOCUMENT) {
          $campagne_from = "0000";
          $campagne_to = "9999";

          $id = "$type-%s-%s";
          return $this->startkey(sprintf($id, $identifiant, $campagne_from))
                          ->endkey(sprintf($id, $identifiant, $campagne_to))
                          ->execute($hydrate);
      }

      public function getDateOuverture($type = self::TYPE_COUCHDB) {
          if ($type == self::TYPE_COUCHDB) {
              $dates = sfConfig::get('app_dates_ouverture_parcellaire_irrigable');
          } else {
            throw new sfException("Le type de parcellaire $type n'existe pas");
          }
          return $dates;
      }


      public function getDateOuvertureDebut($type = self::TYPE_COUCHDB) {
          $dates = $this->getDateOuverture($type);
          return $dates['debut'];
      }

      public function getDateOuvertureFin($type = self::TYPE_COUCHDB) {
          $dates = $this->getDateOuverture($type);
          return $dates['fin'];
      }

      public function isOpen($type = self::TYPE_COUCHDB, $date = null) {
          if (is_null($date)) {
              $date = date('Y-m-d');
          }
          return $date >= $this->getDateOuvertureDebut($type) && $date <= $this->getDateOuvertureFin($type);
      }
}
