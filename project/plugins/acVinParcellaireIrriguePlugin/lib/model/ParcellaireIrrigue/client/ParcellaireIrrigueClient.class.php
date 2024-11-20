<?php

class ParcellaireIrrigueClient extends acCouchdbClient {

      const TYPE_MODEL = "ParcellaireIrrigue";
      const TYPE_COUCHDB = "PARCELLAIREIRRIGUE";

      public static function getInstance() {
          return acCouchdbManager::getClient("parcellaireIrrigue");
      }

      public function createDoc($identifiant, $periode, $papier = false, $date = null, &$errors = null, $type = self::TYPE_COUCHDB) {
      	if (!$date) {
          $date = date('Y-m-d');
        }
        return $this->createOrGetDocFromIdentifiantAndDate($identifiant, $periode, $papier, $date, $errors, $type);
      }

      public function createOrGetDocFromIdentifiantAndDate($identifiant, $periode, $papier = false, $date = null, &$errors = null, $type = self::TYPE_COUCHDB)
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
          if (!$doc_found || $doc_found->periode != $periode) {
	          $parcellaireIrrigue = new parcellaireIrrigue();
	          $parcellaireIrrigue->initDoc($identifiant, $periode, $date, $type);
	          if($papier) {
	          	$parcellaireIrrigue->add('papier', 1);
	          }
          } else {
              $doc_found->date = $date;
              $parcellaireIrrigue = clone $doc_found;
              $parcellaireIrrigue->constructId();
              $parcellaireIrrigue->updateParcelles($errors);
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
}
