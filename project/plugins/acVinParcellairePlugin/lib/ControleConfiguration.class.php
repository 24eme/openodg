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

    public function getLibellePointDeControleFromCodeConstat($codeConstat)
    {
        foreach ($this->configuration['points_de_controle'] as $point) {
            foreach ($point['constats'] as $idConstat => $manquement) {
                if ($idConstat == $codeConstat) {
                    return $point['libelle'];
                }
            }
        }
    }

    public function getLibelleConstat($codeConstat)
    {
        foreach ($this->configuration['points_de_controle'] as $point) {
            foreach ($point['constats'] as $idConstat => $manquement) {
                if ($idConstat == $codeConstat) {
                    return $manquement['libelle'];
                }
            }
        }
    }

    public function getLibelleConstatWithPointId($codeConstat, $pointId)
    {
        return $this->configuration['points_de_controle'][$pointId]['constats'][$codeConstat]['libelle'];
    }

    public function getDelaisConstat($pointId, $codeConstat)
    {
        return $this->configuration['points_de_controle'][$pointId]['constats'][$codeConstat]['delais'];
    }

    public function getConseilConstat($pointId, $codeConstat)
    {
        return '';
    }

    public function getAllLibellesConstats()
    {
        $libellesConstats = array();
        foreach ($this->configuration['points_de_controle'] as $point) {
            foreach ($point['constats'] as $idConstat => $constat) {
                $libellesConstats[$idConstat] = $constat['libelle'];
            }
        }
        return $libellesConstats;
    }
}
