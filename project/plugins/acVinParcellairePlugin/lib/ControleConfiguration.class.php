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

    public function getPointsDeControle()
    {
        return $this->configuration['points_de_controle'];
    }

    public function getLibellePointDeControle($clePointControle)
    {
        return $this->configuration['points_de_controle'][$clePointControle]['libelle'];
    }

    public function getLibelleManquement($clePointControle, $codeRtm)
    {
        return $this->configuration['points_de_controle'][$clePointControle]['rtm'][$codeRtm]['libelle'];
    }

    public function getDelaisManquement($clePointControle, $codeRtm)
    {
        return $this->configuration['points_de_controle'][$clePointControle]['rtm'][$codeRtm]['Délais'];
    }

    public function getConseilManquement($clePointControle, $codeRtm)
    {
        return '';
    }
}
