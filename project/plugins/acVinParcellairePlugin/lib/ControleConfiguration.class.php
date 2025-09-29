<?php

class ControleConfiguration extends DeclarationConfiguration {

    private static $_instance = null;

    public static function getInstance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function getModuleName() {

        return 'controle';
    }

    private function getFromConfig($type, $value = null)
    {
      $items = sfConfig::get('app_controle_'.$type);
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
