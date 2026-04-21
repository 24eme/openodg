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

    public function getAllPointsDeControle()
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

    public function getLibelleConstatWithPointId($codeConstat)
    {
        return $this->getConstat($codeConstat)['libelle'];
    }

    public function getDelaisConstat($codeConstat)
    {
        return $this->getConstat($codeConstat)['delais'];
    }

    public function getConseilConstat($pointId, $codeConstat)
    {
        return '';
    }

    public function getAllLibellesConstats($type_de_controle = false, $type_tournee)
    {
        $type_de_controle = $type_de_controle ? strtolower($type_de_controle) : false;
        $base = [
            'Documentaire' => [
                'Suivi' => [],
                'Habilitation' => []
            ],
            'Terrain' => [
                'Suivi' => [],
                'Habilitation' => []
            ]
        ];

        if ($type_de_controle === 'terrain') {
            $libellesConstats = ['Terrain' => $base['Terrain']];
        } elseif ($type_de_controle === 'documentaire') {
            $libellesConstats = ['Documentaire' => $base['Documentaire']];
        } else {
            $libellesConstats = $base;
        }

        foreach ($this->configuration['points_de_controle'] as $point) {
            if (empty($point['constats'])) continue;

            foreach ($point['constats'] as $idConstat => $constat) {

                $libelle = $constat['libelle'];
                $types = array_flip($constat['types']);

                $domaines = [
                    'Terrain' => !empty($constat['terrain']),
                    'Documentaire' => !empty($constat['documentaire'])
                ];

                foreach ($domaines as $domaine => $actif) {
                    if (! $actif) continue;
                    if (! isset($libellesConstats[$domaine])) continue;
                    if (!isset($types[$type_tournee])) {
                        continue;
                    }
                    $libellesConstats[$domaine][$type_tournee][$idConstat] = $libelle;
                }
            }
        }

        foreach ($libellesConstats as $domaine => $types) {
            foreach ($types as $type => $vals) {
                if (empty($vals)) {
                    unset($libellesConstats[$domaine][$type]);
                }
            }
            if (empty($libellesConstats[$domaine])) {
                unset($libellesConstats[$domaine]);
            }
        }

        return $libellesConstats;
    }

    public function getConstat($codeConstat)
    {
        foreach ($this->configuration['points_de_controle'] as $point) {
            foreach ($point['constats'] as $idConstat => $manquement) {
                if ($idConstat == $codeConstat) {
                    return $manquement;
                }
            }
        }
    }

    public function isTerrain($numRtm)
    {
        return $this->getConstat($numRtm)['terrain'];
    }

    public function isDocumentaire($numRtm)
    {
        return $this->getConstat($numRtm)['documentaire'];
    }

    public function hasProduitFilter() {
        return isset($this->configuration['produit_filter']) && ($this->configuration['produit_filter']);
    }

    public function getProduitFilter() {
        return $this->configuration['produit_filter'];
    }

    public function getMesureOdgFromConstatId($numRtm)
    {
        return $this->getConstat($numRtm)['mesure_odg'];
    }

}
