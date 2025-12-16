<?php

class ControleConfiguration extends DeclarationConfiguration {

    private static $_instance = null;

    public static function getInstance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct() {
        if(!sfConfig::has('controle_configuration_controle')) {
            throw new sfException("La configuration pour le controle n'a pas été défini pour cette application");
        }

        $this->configuration = sfConfig::get('controle_configuration_controle', array());
    }

    public function getModuleName() {

        return 'controle';
    }

    public function getFromConfig($type)
    {
      return sfConfig::get('app_controle_'.$type);
    }

    public function getRtm()
    {
        return $this->configuration['points_de_controle'];
    }

    public function getRtmListePointsDeControle()
    {
        return array_keys($this->getRtm());
    }
}
