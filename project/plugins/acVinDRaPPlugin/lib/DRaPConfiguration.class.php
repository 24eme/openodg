<?php

class DRaPConfiguration extends DeclarationConfiguration {

    private static $_instance = null;

    public static function getInstance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new DRaPConfiguration();
        }
        return self::$_instance;
    }

    public function getCampagneDebutMois() {

        return DRaPConfiguration::getInstance()->getCampagneDebutMois();
    }

    public function getModuleName() {

        return 'drap';
    }

    public function getDateOuvertureConfigName() {

        return 'drap';
    }

    public function getDestinations($value = null)
    {
      return $this->getFromConfig('destinations', $value);
    }

    private function getFromConfig($type, $value = null)
    {
      $items = sfConfig::get('app_drap_'.$type);
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
