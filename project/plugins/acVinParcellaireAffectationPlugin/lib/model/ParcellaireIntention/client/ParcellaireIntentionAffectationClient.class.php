<?php

class ParcellaireIntentionAffectationClient extends acCouchdbClient {

      const TYPE_MODEL = "ParcellaireIntentionAffectation";
      const TYPE_COUCHDB = "PARCELLAIREINTENTIONAFFECTATION";

      public static function getInstance() {
          return acCouchdbManager::getClient("parcellaireIntentionAffectation");
      }

      public function createDoc($identifiant, $periode, $papier = false, $date = null) {
      	if (!$date) {
          $date = date('Y-m-d');
        }
        return $this->createOrGetDocFromIdentifiantAndDate($identifiant, $periode, $papier, $date);
      }

      public function createOrGetDocFromIdentifiantAndDate($identifiant, $periode, $papier = false, $date = null)
      {
          $doc_found = $this->findPreviousByIdentifiantAndDate($identifiant, $date);
          if ($doc_found && $doc_found->date === $date) {
              return $doc_found;
          }
          if (!$doc_found) {
	          $parcellaireIntentionAffectation = new ParcellaireIntentionAffectation();
	          $parcellaireIntentionAffectation->initDoc($identifiant, $periode, $date);
              $parcellaireIntentionAffectation->storeDeclarant();
	          $parcellaireIntentionAffectation->add('papier', 1);
          } else {
              $doc_found->date = $date;
              $parcellaireIntentionAffectation = clone $doc_found;
              $parcellaireIntentionAffectation->initDoc($identifiant, $periode, $date);
              $parcellaireIntentionAffectation->updateParcelles();
          }
          //$parcellaireIntentionAffectation->save();
          return $parcellaireIntentionAffectation;
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
