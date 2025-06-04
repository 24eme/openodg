<?php

class ParcellaireManquantClient extends acCouchdbClient {

      const TYPE_MODEL = "ParcellaireManquant";
      const TYPE_COUCHDB = "PARCELLAIREMANQUANT";
      const TYPE_LIBELLE = "Déclaration de pieds manquants";

      public static function getInstance() {
          return acCouchdbManager::getClient("ParcellaireManquant");
      }

      public function findOrCreate($identifiant, $periode, $type = self::TYPE_COUCHDB) {
          if (strlen($periode) != 4)
              throw new sfException("La periode doit être une année et non " . $periode);
          $parcellaireManquant = $this->find($this->buildId($identifiant, $periode, $type));
          if (is_null($parcellaireManquant)) {
              $parcellaireManquant = $this->createDoc($identifiant, $periode, $type);
          }

          return $parcellaireManquant;
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
          $parcellaireManquant = new ParcellaireManquant();
          $parcellaireManquant->initDoc($identifiant, $periode, $type);

          if($papier) {
          	$parcellaireManquant->add('papier', 1);
          }

          return $parcellaireManquant;
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
              $dates = sfConfig::get('app_dates_ouverture_parcellaire_manquant');
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

      private function getFromConfig($type, $value = null)
      {
      	$items = sfConfig::get('app_parcellaire_manquant_'.$type);
      	$entries = array();
      	foreach ($items as $item) {
      		$entry = new stdClass();
      		$entry->id = $item;
      		$entry->text = $item;
      		$entries[] = $entry;
      	}
      	if ($value) {
      		$entry = new stdClass();
      		$entry->id = $value;
      		$entry->text = $value;
      		$entries[] = $entry;
      	}
      	return $entries;
      }
}
