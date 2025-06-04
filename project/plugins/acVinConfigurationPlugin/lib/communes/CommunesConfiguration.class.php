<?php

class CommunesConfiguration {

    private static $_instance = null;
    protected $communes;
    protected $communes_reverse;
    protected $secteurs;

    public static function getInstance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new CommunesConfiguration();
        }
        return self::$_instance;
    }

    public static function retrieveCommunesFromOpenDataWine($denomination) {
        if (strlen($denomination) < 5)  {
            $denomination = sprintf('%05d', $denomination);
        }
        $url_communes = "https://raw.githubusercontent.com/24eme/opendatawine/master/denominations/".$denomination.".json";
        $contents = @file_get_contents($url_communes);
        return (array)json_decode($contents);
    }

    public static function retrieveCommunesFromCachedOpenDataWine() {
        $communes = array();
        foreach (ParcellaireConfiguration::getInstance()->getAiresInfos() as $k => $a) {
            $communes = $communes + CacheFunction::cache('model', "CommunesConfiguration::retrieveCommunesFromOpenDataWine", array($a['denomination_id']));
        }
        return $communes;
    }

    public function __construct() {
        $this->config = sfConfig::get('configuration_communes', array());
        $this->communes = [];
        if (isset($this->config['insee2commune'])) {
            $this->communes = $this->config['insee2commune'];
        }
        if (!count($this->communes)) {
            $this->communes = self::retrieveCommunesFromCachedOpenDataWine();
        }
        $this->communes_reverse = array_flip($this->communes);
        $this->secteurs = [];
        if (isset($this->config['insee2secteur'])) {
            $this->secteurs = $this->config['insee2secteur'];
        }
    }

    public function getByCodeCommune() {

        return $this->communes;
    }

    public function getCommuneByCode($c) {

        return $this->communes[$c];
    }

    public function findCodeCommune($commune) {
        if(!isset($this->communes_reverse[$commune])) {
            $commune_simplified = $commune;
            $commune_simplified = strtoupper($commune_simplified);
            $commune_simplified = preg_replace('/^ST(E?) /', 'SAINT\1 ', $commune_simplified);
            $commune_simplified = preg_replace('/[^A-Z]/', '', $commune_simplified);
            foreach($this->communes_reverse as $c => $v) {
                $c = preg_replace('/[^A-Z]/', '', strtoupper($c));
                if ( (strpos($c, $commune_simplified) !== false) || (strpos($commune_simplified, $c) !== false) ) {
                    $this->communes_reverse[$commune] = $v;
                    return $v;
                }
            }
            return null;
        }

        return $this->communes_reverse[$commune];
    }

    public function hasSecteurs() {
        return (count($this->secteurs));
    }

    public function getSecteurFromInsee($i) {
        if (!isset($this->secteurs[$i])) {
            return null;
        }
        return $this->secteurs[$i];
    }

}
