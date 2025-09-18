<?php

class CommunesConfiguration {

    private static $_instance = null;
    protected $communes;
    protected $communes_reverse;

    public static function getInstance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new CommunesConfiguration();
        }
        return self::$_instance;
    }

    public function __construct() {
        $this->config = sfConfig::get('configuration_communes', array());
        $this->communes = []
        if (isset($this->config['insee2commune'])) {
            $this->communes = $this->config['insee2commune'];
        }
        $this->communes_reverse = array_flip($this->communes);
    }

    public function getByCodeCommune() {

        return $this->communes;
    }

    public function findCodeCommune($commune) {
        if(!isset($this->communes_reverse[$commune])) {

            return null;
        }

        return $this->communes_reverse[$commune];
    }

}
