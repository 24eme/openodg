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

      public function createDoc($identifiant, $campagne, $papier = false, $type = self::TYPE_COUCHDB) {
          $parcellaireIrrigable = new parcellaireIrrigable();
          $parcellaireIrrigable->initDoc($identifiant, $campagne, $type);
          
          if($papier) {
          	$parcellaireIrrigable->add('papier', 1);
          }

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

      public function getRessources($value = null) 
      {
      	return $this->getFromConfig('ressources', $value);
      }

      public function getMateriels($value = null) 
      {
      	return $this->getFromConfig('materiels', $value);
      }
      
      private function getFromConfig($type, $value = null)
      {
      	$items = sfConfig::get('app_parcellaire_irrigable_'.$type);
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
