<?php

/**
 * Crée le fichier ODS de qui calcule les potentiels de production
 */
class ExportParcellairePPODS extends BaseExportParcellaireODS {

    public function __construct($parcellaire) {
        parent::__construct($parcellaire, 'calculatrice_PP.ods');
    }

    protected function parseDocument() {

        // Intitialise les valeurs des clés
        $synthese = [
            '%%GRENACHE_N' => 0.0,
            '%%SYRAH_N' => 0.0,
            '%%MOURVEDRE_N' => 0.0,
            '%%TIBOUREN_N' => 0.0,
            '%%CINSAULT_N' => 0.0,
            '%%CARIGNAN_N' => 0.0,
            '%%CABERNET_SAUVIGNON_N' => 0.0,
            '%%CALITOR_N' => 0.0,
            '%%BARBAR_RS' => 0.0,
            '%%VERMENTINO_B' => 0.0,
            '%%UGNI_B' => 0.0,
            '%%CLAIRETTE_B' => 0.0,
            '%%SEMILLON_B' => 0.0,
        ];

        // Rempli le tableau avec les superficie des cepages qu'on a
        foreach ($this->getParcellaire()->getParcelles() as $p) {
            if (ParcellaireConfiguration::getInstance()->isTroisiemeFeuille() && !$p->hasTroisiemeFeuille()) {
                continue;
            }

            $key = '%%' . str_replace(' ', '_', strtoupper($p->getCepage()));

            if (!isset($synthese[$key])) {
                $synthese[$key] = 0;
            }
            $synthese[$key] = round($synthese[$key] + $p->superficie, 6);
        }

        $this->parse($synthese);
    }

}