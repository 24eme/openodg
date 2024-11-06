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
        $this->nav_configuration['stats'] = array();
        if (sfContext::getInstance()->getUser()->hasCredential(myUser::CREDENTIAL_ADMIN)) {
            $this->nav_configuration['stats'] = sfConfig::get($this->getAppPath(), array());
        }
        $regions = array();
        if (sfContext::getInstance()->getUser()->hasCredential(myUser::CREDENTIAL_ADMIN) && class_exists("RegionConfiguration")) {
            $regions = RegionConfiguration::getInstance()->getOdgRegions();
        }elseif (sfContext::getInstance()->getUser()->getRegion()) {
            $regions = array(sfContext::getInstance()->getUser()->getRegion());
        }
        if (count($regions)) {
            $this->nav_configuration['stats'][] = array('name' => 'Export Tiers', 'url' => '#', 'title' => 1);
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
