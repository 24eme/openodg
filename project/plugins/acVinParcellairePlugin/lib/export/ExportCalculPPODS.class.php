<?php

/**
 * Crée le fichier ODS de qui calcule les potentiels de production
 */
class ExportCalculPPODS extends ExportGenericParcellaireODS {

    private $identificationParcellaire;
    private $etablissement;
    private $dgc;
    private $cepages;

    public function __construct($dgc, $cepages) {
        parent::__construct(null, 'FT-22-V1_Calculatrice_PP_'.$dgc.'.ods');
        $this->dgc = $dgc;
        $this->cepages = $cepages;
    }

    protected function parseDocument() {

        // Intitialise les valeurs des clés pour les cepages CDP et DGC
        $synthese = [
            '%%GRENACHE_N' => 0.0,
            '%%SYRAH_N' => 0.0,
            '%%MOURVEDRE_N' => 0.0,
            '%%TIBOUREN_N' => 0.0,
            '%%CINSAUT_N' => 0.0,
            '%%CARIGNAN_N' => 0.0,
            '%%CABERNET_SAUVIGNON_N' => 0.0,
            '%%CALITOR_NOIR_N' => 0.0,
            '%%BARBAROUX_RS' => 0.0,
            '%%ROUSSELI_RS' => 0.0,
            '%%CALADOC_N' => 0.0,
            '%%AGIORGITIKO_N' => 0.0,
            '%%CALABRESE_N' => 0.0,
            '%%MOSCHOFILERO_RS' => 0.0,
            '%%XINOMAVRO_N' => 0.0,
            '%%VERDEJO_B' => 0.0,
            '%%VERMENTINO_B' => 0.0,
            '%%UGNI_BLANC_B' => 0.0,
            '%%CLAIRETTE_B' => 0.0,
            '%%SEMILLON_B' => 0.0,
        ];

        // Rempli le tableau avec les superficie des cepages qu'on a
        foreach ($this->cepages as $c => $ha) {
            if (! $ha) {
                continue;
            }
            $key = $this->getKeyFromCepage($c);

            if (!isset($synthese[$key])) {
                $synthese[$key] = 0.0;
            }
            if ($ha) {
                $synthese[$key] = round(floatval($ha), 6);
            }
        }

        $this->parse($synthese);
    }

    /**
     * Crée la clé qu'on va retrouver dans l'ODS à partir du nom du cépage
     *
     * @param string $libelle_cepage : le libellé du cepage (CINSAULT N, CARIGNAN N, ...)
     */
    private function getKeyFromCepage($libelle_cepage, $prefix='') {
        return '%%' . $prefix . str_replace(' ', '_', strtoupper($libelle_cepage));
    }

}