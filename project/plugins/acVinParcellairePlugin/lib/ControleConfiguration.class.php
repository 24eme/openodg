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

    public function getLibellePointDeControleFromCodeRtm($codeRtm)
    {
        foreach ($this->configuration['points_de_controle'] as $point) {
            foreach ($point['rtm'] as $idrtm => $manquement) {
                if ($idrtm == $codeRtm) {
                    return $point['libelle'];
                }
            }
        }
    }

    public function getLibelleManquement($codeRtm)
    {
        foreach ($this->configuration['points_de_controle'] as $point) {
            foreach ($point['rtm'] as $idrtm => $manquement) {
                if ($idrtm == $codeRtm) {
                    return $manquement['libelle'];
                }
            }
        }
    }

    public function getLibelleManquementWithPointId($codeRtm, $pointId)
    {
        return $this->configuration['points_de_controle'][$pointId]['rtm'][$codeRtm]['libelle'];
    }

    public function getDelaisManquement($pointId, $codeRtm)
    {
        return $this->configuration['points_de_controle'][$pointId]['rtm'][$codeRtm]['delais'];
    }

    public function getConseilManquement($pointId, $codeRtm)
    {
        return '';
    }

    public function getAllLibellesManquements()
    {
        $libellesManquements = array();
        foreach ($this->configuration['points_de_controle'] as $point) {
            foreach ($point['rtm'] as $idrtm => $manquement) {
                $libellesManquements[$idrtm] = $manquement['libelle'];
            }
        }
        return $libellesManquements;
    }
}
