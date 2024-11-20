<?php

class ParcellaireIrrigableClient extends acCouchdbClient {

      const TYPE_MODEL = "ParcellaireIrrigable";
      const TYPE_COUCHDB = "PARCELLAIREIRRIGABLE";
      const TYPE_LIBELLE = "Déclaration d'irrigation";

      public static function getInstance() {
          return acCouchdbManager::getClient("parcellaireIrrigable");
      }

      public function findOrCreate($identifiant, $periode, $type = self::TYPE_COUCHDB) {
          if (strlen($periode) != 4)
              throw new sfException("La periode doit être une année et non " . $periode);
          $parcellaireIrrigable = $this->find($this->buildId($identifiant, $periode, $type));
          if (is_null($parcellaireIrrigable)) {
              $parcellaireIrrigable = $this->createDoc($identifiant, $periode, $type);
          }

          return $parcellaireIrrigable;
      }


      public function buildId($identifiant, $periode, $type = self::TYPE_COUCHDB) {
          $id = "$type-%s-%s";
          return sprintf($id, $identifiant, $periode);
      }

      public function createDocFromPrevious($identifiant, $periode, $papier = false, $type = self::TYPE_COUCHDB) {
          $new = $this->createDoc($identifiant, $periode, $papier, $type);
          $previous = $this->getLast($identifiant);
          if ($previous) {
              $new->declaration = clone $previous->declaration;
          }
          return $new;
      }

      public function createDoc($identifiant, $periode, $papier = false, $type = self::TYPE_COUCHDB) {
          $parcellaireIrrigable = new parcellaireIrrigable();
          $parcellaireIrrigable->initDoc($identifiant, $periode, $type);

          if($papier) {
          	$parcellaireIrrigable->add('papier', 1);
          }

          return $parcellaireIrrigable;
      }

      public function getLast($identifiant, $max_annee = '9999', $hydrate = acCouchdbClient::HYDRATE_DOCUMENT){
          return $this->findPreviousByIdentifiantAndDate($identifiant, $max_annee, $hydrate);
      }

      public function findPreviousByIdentifiantAndDate($identifiant, $max_annee = '9999', $hydrate = acCouchdbClient::HYDRATE_DOCUMENT) {
          $h = $this->getHistory($identifiant, $max_annee, $hydrate);
          if (!count($h)) {
            return null;
          }
          $h = $h->getDocs();
          end($h);
          $doc = $h[key($h)];
          return $doc;
      }

      public function getHistory($identifiant, $max_annee = '9999', $hydrate = acCouchdbClient::HYDRATE_DOCUMENT) {
          $periode_from = "0000";
          $periode_to = $max_annee;

          $id = self::TYPE_COUCHDB.'-%s-%s';
          return $this->startkey(sprintf($id, $identifiant, $periode_from))
                          ->endkey(sprintf($id, $identifiant, $periode_to))
                          ->execute($hydrate);
      }


}
