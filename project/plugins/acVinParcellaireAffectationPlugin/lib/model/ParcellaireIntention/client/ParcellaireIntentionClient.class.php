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

      public function getLastRealIntention($identifiant, $max_annee = '9999', $hydrate = acCouchdbClient::HYDRATE_DOCUMENT){
          return ParcellaireIntentionAffectationClient::getInstance()->getLast($identifiant, $max_annee, $hydrate);
      }

      public function getLast($identifiant, $max_annee = '9999', $hydrate = acCouchdbClient::HYDRATE_DOCUMENT){
          if (ParcellaireConfiguration::getInstance()->affectationNeedsIntention()) {
              return ParcellaireIntentionAffectationClient::getInstance()->getLast($identifiant, $max_annee, $hydrate);
          }
          return $this->createIntentionAuto($identifiant, $max_annee, date('Y-m-d'));
      }
}
