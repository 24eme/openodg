<?php

class NavConfiguration {

    private static $_instance = null;
    protected $configuration;

    public static function getInstance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new NavConfiguration();
        }
        return self::$_instance;
    }

    public function getAppPath() {
        return 'app_nav_stats_'.sfConfig::get('sf_app');
    }

    public function load() {
        $this->nav_configuration = array();
        if (sfContext::getInstance()->getUser()->hasCredential(myUser::CREDENTIAL_ADMIN)) {
            $regions = RegionConfiguration::getInstance()->getOdgRegions();
            $this->nav_configuration['stats'] = sfConfig::get($this->getAppPath(), array());
        }
        $regions = array(sfContext::getInstance()->getUser()->getTeledeclarationDrevRegion());
        if (count($regions)) {
            $this->nav_configuration['stats'][] = array('name' => 'Export Crus', 'url' => '#', 'title' => 1);
            foreach($regions as $r) {
                $this->nav_configuration['stats'][] = array('name' => 'Tableurs '.$r, 'url' => '/exports_'.$r.'/');
            }
        }
        return ;
    }

    public function __construct() {
        $this->load();
    }
    
    public function getStatLinks() {
        return $this->nav_configuration['stats'];
    }
    
    public function hasStatLinks() {
        return count($this->nav_configuration['stats']) > 1;
    }
}
