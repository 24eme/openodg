<?php

class ParcellaireIrrigueClient extends acCouchdbClient {

      const TYPE_MODEL = "ParcellaireIrrigue";
      const TYPE_COUCHDB = "PARCELLAIREIRRIGUE";

      public static function getInstance() {
          return acCouchdbManager::getClient("parcellaireIrrigue");
      }

      public function createDoc($identifiant, $campagne, $papier = false, $date = null, $type = self::TYPE_COUCHDB) {
      	if (!$date) {
          $date = date('Y-m-d');
        }
        return $this->createOrGetDocFromIdentifiantAndDate($identifiant, $campagne, $papier, $date, $type);
      }

      public function createOrGetDocFromIdentifiantAndDate($identifiant, $campagne, $papier = false, $date = null, $type = self::TYPE_COUCHDB)
      {
          $doc_found = $this->findPreviousByIdentifiantAndDate($identifiant, $date);
          if ($doc_found && $doc_found->date === $date) {
              $doc_found->updateParcelles();
              if($papier) {
                  $doc_found->add('papier', 1);
              } else {
                  $doc_found->add('papier', 0);
              }
              return $doc_found;
          }
          if (!$doc_found || $doc_found->campagne != $campagne) {
	          $parcellaireIrrigue = new parcellaireIrrigue();
	          $parcellaireIrrigue->initDoc($identifiant, $campagne, $date, $type);
	          if($papier) {
	          	$parcellaireIrrigue->add('papier', 1);
	          }
          } else {
              $doc_found->date = $date;
              $parcellaireIrrigue = clone $doc_found;
              $parcellaireIrrigue->constructId();
              $parcellaireIrrigue->updateParcelles();
              if($papier) {
                  $parcellaireIrrigue->add('papier', 1);
              } else {
                  $parcellaireIrrigue->add('papier', 0);
              }
          }
          return $parcellaireIrrigue;
      }

      public function getLast($identifiant, $max_annee = '9999', $hydrate = acCouchdbClient::HYDRATE_DOCUMENT){
          return $this->findPreviousByIdentifiantAndDate($identifiant, $max_annee.'-99-99', $hydrate);
      }

      public function findPreviousByIdentifiantAndDate($identifiant, $date, $hydrate = acCouchdbClient::HYDRATE_DOCUMENT) {
          $h = $this->getHistory($identifiant, $date, $hydrate);
          if (!count($h)) {
            return null;
          }
          $h = $h->getDocs();
          end($h);
          $doc = $h[key($h)];
          return $doc;
      }

      public function getHistory($identifiant, $date = '9999-99-99', $hydrate = acCouchdbClient::HYDRATE_DOCUMENT, $dateDebut = "0000-00-00") {
          return $this
          			->startkey(sprintf(self::TYPE_COUCHDB."-%s-%s", $identifiant, str_replace('-', '', $dateDebut)))
                    ->endkey(sprintf(self::TYPE_COUCHDB."-%s-%s", $identifiant, str_replace('-', '', $date)))
          			->execute($hydrate);
      }

      public function getDateOuverture($type = self::TYPE_COUCHDB) {
          if ($type == self::TYPE_COUCHDB) {
              $dates = sfConfig::get('app_dates_ouverture_parcellaire_irrigue');
          }
          if (!is_array($dates) || !isset($dates['debut']) || !isset($dates['fin'])) {
              return array('debut'=>'1900-01-01', 'fin' => '9999-12-31');
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
