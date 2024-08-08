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
        $this->communes = sfConfig::get('configuration_communes', array());
        if (!count($this->communes)) {
            $this->communes = self::retrieveCommunesFromCachedOpenDataWine();
        }
        $this->communes_reverse = array_flip($this->communes);
    }

    public function getByCodeCommune() {

        return $this->communes;
    }

    public function getCommuneByCode($c) {

        return $this->communes[$c];
    }

    public function findCodeCommune($commune) {
        if(!isset($this->communes_reverse[$commune])) {
            $commune = strtoupper($commune);
            $commune = preg_replace('/^ST /', 'SAINT ', $commune);
            $commune = preg_replace('/[^A-Z]/', '', $commune);
            foreach($this->communes_reverse as $c => $v) {
                $c = preg_replace('/[^A-Z]/', '', strtoupper($c));
                if (strpos($c, $commune) !== false) {
                    return $v;
                }
            }
            return null;
        }

        return $this->communes_reverse[$commune];
    }

}
