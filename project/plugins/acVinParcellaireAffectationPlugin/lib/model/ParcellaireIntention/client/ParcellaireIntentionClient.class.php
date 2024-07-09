<?php

class ParcellaireIntentionClient {

      private static $_instance;

      public static function getInstance() {
          if (!self::$_instance) {
              self::$_instance = new ParcellaireIntentionClient();
          }
          return self::$_instance;
      }

      public function createDoc($identifiant, $periode, $papier = false, $date = null, $type = ParcellaireIntentionAffectationClient::TYPE_COUCHDB) {
      	if (!$date) {
          $date = date('Y-m-d');
        }
        return $this->createOrGetDocFromIdentifiantAndDate($identifiant, $periode, $papier, $date, $type);
      }

      public function createOrGetDocFromIdentifiantAndDate($identifiant, $periode, $papier = false, $date = null, $type = ParcellaireIntentionAffectationClient::TYPE_COUCHDB) {
          if (ParcellaireConfiguration::getInstance()->affectationNeedsIntention()) {
              return ParcellaireIntentionAffectationClient::getInstance()->createOrGetDocFromIdentifiantAndDate($identifiant, $periode, $papier, $date);
          }
          return $this->createIntentionAuto($identifiant, $periode, $date);
      }

      public function createIntentionAuto($identifiant, $periode, $date) {
          if ($periode > 9000) {
              $periode = date('Y');
          }
          $intentionAuto = new ParcellaireIntentionAuto();
          $intentionAuto->initDoc($identifiant, $periode, $date);
          $intentionAuto->updateParcelles();
          return $intentionAuto;
      }

      public function getLast($identifiant, $max_annee = '9999', $hydrate = acCouchdbClient::HYDRATE_DOCUMENT){
          if (ParcellaireConfiguration::getInstance()->affectationNeedsIntention()) {
              return ParcellaireIntentionAffectationClient::getInstance()->getLast($identifiant, $max_annee, $hydrate);
          }
          return $this->createIntentionAuto($identifiant, $max_annee, date('Y-m-d'));
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
