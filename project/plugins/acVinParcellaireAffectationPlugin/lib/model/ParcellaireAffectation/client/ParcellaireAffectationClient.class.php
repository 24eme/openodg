<?php

class ParcellaireAffectationClient extends acCouchdbClient {

      const TYPE_MODEL = "ParcellaireAffectation";
      const TYPE_COUCHDB = "PARCELLAIREAFFECTATION";
      const TYPE_LIBELLE = "Déclaration d'affection parcellaire";

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
          $parcellaireAffectation = new ParcellaireAffectation();
          $parcellaireAffectation->initDoc($identifiant, $periode, $type);
          $parcellaireAffectation->add('papier', ($papier) * 1);
          return $parcellaireAffectation;
      }

      public function getLast($identifiant, $max_annee = '9999', $hydrate = acCouchdbClient::HYDRATE_DOCUMENT){
          return $this->findPreviousByIdentifiantAndDate($identifiant, $max_annee, $hydrate);
      }

      public function findPreviousByIdentifiantAndDate($identifiant, $date = null, $hydrate = acCouchdbClient::HYDRATE_DOCUMENT) {
          $max_annee = '9999';
          if (!$date) {
              $max_annee = date('Y');
          }
          if (strlen($date) == 4) {
              $max_annee = $date;
          }
          if (preg_match('/(....)-(..-..)/', $date, $m)) {
              if ($m[2] < '08-01') {
                  $max_annee = $m[1] - 1;
              }else{
                  $max_annee = $m[1];
              }
          }
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

      public function needAffectation($identifiant, $periode) {
          if(!ParcellaireConfiguration::getInstance()->isParcellesFromAffectationparcellaire()) {
              return false;
          }

          $affectation = ParcellaireAffectationClient::getInstance()->find(ParcellaireAffectationClient::getInstance()->buildId($identifiant, $periode), acCouchdbClient::HYDRATE_JSON);

          return !$affectation || !$affectation->validation_odg;
      }
}
