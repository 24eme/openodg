<?php

class DegustationConfiguration {

    private static $_instance = null;
    protected $configuration;

    public static function getInstance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new DegustationConfiguration();
        }
        return self::$_instance;
    }

    public function __construct() {
        if(!sfConfig::has('degustation_configuration_degustation')) {
			throw new sfException("La configuration pour les degustations n'a pas été définie pour cette application");
		}

        $this->configuration = sfConfig::get('degustation_configuration_degustation', array());
    }

    public function getColleges() {

        return (isset($this->configuration['colleges']))? $this->configuration['colleges'] : array();
    }

    public function getLibelleCollege($key) {
        $colleges = $this->getColleges();
        return (isset($colleges[$key]))? $colleges[$key] : '';
    }

    public function hasSpecificiteLotPdf(){
      return isset($this->configuration['specificite_lot_pdf']) && $this->configuration['specificite_lot_pdf'];
    }

    public function hasAnonymat4labo()
    {
        return isset($this->configuration['anonymat4labo']) && boolval($this->configuration['anonymat4labo']);
    }

    public function hasNotation()
    {
        return isset($this->configuration['notation']) && boolval($this->configuration['notation']);
    }

    public function getLieux() {

        return CacheFunction::cache('model', array(DegustationConfiguration::getInstance(), '_getLieux'), [Organisme::getInstance()->getCurrentRegion()]);
    }

    public function hasNotification()
    {
        return isset($this->configuration['has_notification']) && boolval($this->configuration['has_notification']);
    }

    public function _getLieux($region) {
        $degusts = DegustationClient::getInstance()->getHistory(50, '', acCouchdbClient::HYDRATE_ON_DEMAND_JSON, $region);
        $lieux = array();
        foreach ($degusts as $d) {
            $lieux[$d->lieu] = $d->lieu;
        }
        if (!count($lieux)) {
            return array("Salle de dégustation par défaut" => "Salle de dégustation par défaut");
        }
        return $lieux;
    }

    public function isAnonymisationManuelle()
    {
        return $this->configuration['anonymisation_manuelle'] === true;
    }

    public function getFormatAnonymat()
    {
        return isset($this->configuration['anonymisation_format']) ? $this->configuration['anonymisation_format'] : '';
    }

    public function hasTypiciteCepage()
    {
        return $this->configuration['typicite_cepage'] === true;
    }

    public function getConformites()
    {
        return $this->configuration['conformite'];
    }

    public function getNbEtiquettes()
    {
        return $this->configuration['nb_etiquettes'];
    }

    public function isTourneeAutonome() {
        return $this->configuration['tournee_autonome'];
    }

    public function isDegustationAutonome() {
        return $this->configuration['tournee_autonome'];
    }

    public function hasStaticRegion() {
        return isset($this->configuration['static_region']) && boolval($this->configuration['static_region']);
    }

    public function getStaticRegion() {
        if($this->hasStaticRegion()){
          return $this->configuration['static_region'];
        }
    }

    public function hasAlwaysIdentifiantTable() {
        if (!isset($this->configuration['always_identifiant_table'])) {
            return false;
        }
        return $this->configuration['always_identifiant_table'];
    }

    public function hasDegustateursPrerempli()
    {
        return isset($this->configuration['has_degustateurs_prerempli']) && $this->configuration['has_degustateurs_prerempli'];
    }

    public function hasAcceptabiliteAoc()
    {
        return isset($this->configuration['is_acceptable_aoc']) && $this->configuration['is_acceptable_aoc'];
    }

    public function getAcceptabiliteAoc()
    {
        if ($this->hasAcceptabiliteAoc()) {
            return $this->configuration['is_acceptable_aoc'];
        }
    }
}
