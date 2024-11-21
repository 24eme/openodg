<?php

class ParcellaireIrrigableConfiguration extends DeclarationConfiguration {

    private static $_instance = null;

    public static function getInstance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new ParcellaireIrrigableConfiguration();
        }
        return self::$_instance;
    }

    public function getCampagneDebutMois() {

        return ParcellaireAffectationConfiguration::getInstance()->getCampagneDebutMois();
    }

    public function getModuleName() {

        return 'parcellaireIrrigable';
    }

    public function getDateOuvertureConfigName() {

        return 'parcellaire_irrigable';
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
