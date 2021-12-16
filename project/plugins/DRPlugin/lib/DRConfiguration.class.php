<?php

class DRConfiguration
{
    private static $_instance = null;
    protected $configuration;

    public static function getInstance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new DRConfiguration();
        }

        return self::$_instance;
    }

    public function load()
    {
        $this->configuration = sfConfig::get('dr_configuration_dr', []);
    }

    private function __construct()
    {
        if(! sfConfig::has('dr_configuration_dr')) {
            throw new sfException("La configuration pour les dr n'a pas été définie pour cette application");
        }

        $this->load();
    }

    public function hasValidationDR()
    {
        return isset($this->configuration['validation']) && $this->configuration['validation'];
    }
}
