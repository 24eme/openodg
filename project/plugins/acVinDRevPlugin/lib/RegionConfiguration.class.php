<?php

class RegionConfiguration {

    private static $_instance = null;
    protected $configuration;

    public static function getInstance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new RegionConfiguration();
        }
        return self::$_instance;
    }

    public function load() {
        $this->configuration = sfConfig::get('region_configuration_region', array());
    }

    public function __construct() {
        if(!sfConfig::has('region_configuration_region')) {
			throw new sfException("La configuration pour les regions n'a pas été défini pour cette application");
		}

        $this->load();
    }

    public function hasOC()
    {
        return isset($this->configuration['oc']) && boolval($this->configuration['oc']);
    }

    public function getOC()
    {
        return $this->hasOC() ? $this->configuration['oc'] : false;
    }

    public function hasOdgProduits() {
      return isset($this->configuration['odg']) && count($this->configuration['odg']);
    }

    public function getOdgProduits($odgName) {
      if(!isset($this->configuration['odg']) || !array_key_exists($odgName,$this->configuration['odg']) || !isset($this->configuration['odg'][$odgName]['produits']) ){
        return array();
      }
      return $this->configuration['odg'][$odgName]['produits'];
    }

    public function isHashProduitInRegion($odgName, $hashProduit) {
        foreach($this->getOdgProduits($odgName) as $hash) {
            if(preg_match("|".$hash."|", $hashProduit))  {
                return true;
            }
        }

        return false;
    }

    public function getOdgHabilitationProduits($odgName) {
        if(!isset($this->configuration['odg']) || !array_key_exists($odgName,$this->configuration['odg']) || !isset($this->configuration['odg'][$odgName]['habilitation_produits']) ){
          return array();
        }
        return $this->configuration['odg'][$odgName]['habilitation_produits'];        
    }

    public function getOdgINAOHabilitationFile($odgName) {
      if(!isset($this->configuration['odg']) || !array_key_exists($odgName,$this->configuration['odg']) || !isset($this->configuration['odg'][$odgName]['inao']) ){
        return null;
      }
      return $this->configuration['odg'][$odgName]['inao'];
    }

    public function getOdgRegions(){
      if(!$this->hasOdgProduits()){
        return array();
      }
      return array_keys($this->configuration['odg']);
    }
    
    public function getOdgRegionInfos($region){
        if(!isset($this->configuration['odg']) || !array_key_exists($region,$this->configuration['odg']) || !isset($this->configuration['odg'][$region]) ){
            return null;
        }
        $odgInfos = array();
        foreach ($this->configuration['odg'][$region] as $key => $value) {
          if(is_string($value) && preg_match("/^%.+%$/",$value)){
            $odgInfos[$key] = sfConfig::get(str_replace("%",'',$value), '');
          }else{
            $odgInfos[$key] = $value;
          }
        }
        return $odgInfos;
    }

    public function filterMouvementsByRegion($mouvements, $region)
    {
        return array_filter($mouvements, function($mouvement) use ($region) {
            if (property_exists($mouvement->value, 'region') === false) { return false; }
            return strpos($mouvement->value->region, $region) !== false;
        });
    }

    public function getOdgRegion($hash_produit)
    {
        $regions = array();
        if ($this->configuration['odg']) {
            $regions = array_filter($this->configuration['odg'], function ($v) use ($hash_produit) {
            return count(array_filter($v['produits'], function ($p) use ($hash_produit) {
                return strpos($hash_produit, $p) !== false;
            })) > 0;
            });
        }

        if (count($regions) > 1) {
            throw new Exception("Plusieurs régions pour le produit ".$hash_produit);
        }

        return array_key_first($regions);
    }


    public function getOdgConfigurationItem($odgName, $configurationItem) {
        if(!isset($this->configuration['odg']) || !array_key_exists($odgName,$this->configuration['odg']) || !isset($this->configuration['odg'][$odgName][$configurationItem]) ){
          return null;
        }
        return $this->configuration['odg'][$odgName][$configurationItem];
    }
}
