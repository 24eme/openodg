<?php

class ParcellaireAffectationClient extends acCouchdbClient {

      const TYPE_MODEL = "ParcellaireAffectation";
      const TYPE_COUCHDB = "PARCELLAIREAFFECTATION";

      public static function getInstance() {
          return acCouchdbManager::getClient("ParcellaireAffectation");
      }

      public function findOrCreate($identifiant, $periode, $papier = false, $type = self::TYPE_COUCHDB) {
          if (strlen($periode) != 4)
              throw new sfException("La periode doit être une année et non " . $periode);
          $parcellaireAffectation = $this->find($this->buildId($identifiant, $periode, $type));
          if (is_null($parcellaireAffectation)) {
              $parcellaireAffectation = $this->createDoc($identifiant, $periode, $papier, $type);
          }

          return $parcellaireAffectation;
      }

      public function buildId($identifiant, $periode, $type = self::TYPE_COUCHDB) {
          $id = "$type-%s-%s";
          return sprintf($id, $identifiant, $periode);
      }

      public function createDoc($identifiant, $periode, $papier = false, $type = self::TYPE_COUCHDB) {
          $previous = $this->findPreviousByIdentifiantAndDate($identifiant, $periode-1);
          if ($previous && $previous->isValidee()) {
              $previous->devalidate();
              $previous->campagne = $periode.'-'.($periode + 1);
              $parcellaireAffectation = clone $previous;
              $parcellaireAffectation->constructId();
              $parcellaireAffectation->updateParcellesAffectation();
          } else {
            $parcellaireAffectation = new ParcellaireAffectation();
            $parcellaireAffectation->initDoc($identifiant, $periode, $type);
          }
          $parcellaireAffectation->add('papier', ($papier) * 1);
          return $parcellaireAffectation;
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

      public function getDateOuverture($type = self::TYPE_COUCHDB) {
          if ($type == self::TYPE_COUCHDB) {
              $dates = sfConfig::get('app_dates_ouverture_parcellaire_affectation');
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
