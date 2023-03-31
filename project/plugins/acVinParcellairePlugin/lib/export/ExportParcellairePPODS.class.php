<?php

/**
 * Crée le fichier ODS de qui calcule les potentiels de production
 */
class ExportParcellairePPODS extends BaseExportParcellaireODS {

    private $identificationParcellaire;
    private $etablissement;
    private $dgc;

    public function __construct($parcellaire) {
        parent::__construct($parcellaire, 'calculatrice_PP.ods');
        $this->etablissement = $parcellaire->getEtablissementObject();
        $this->identificationParcellaire = ParcellaireAffectationClient::getInstance()->getLast($this->etablissement->identifiant);
        if (!$this->identificationParcellaire) {
            $this->identificationParcellaire = ParcellaireIntentionAffectationClient::getInstance()->getLast($this->etablissement->identifiant);
        }
        if(isset($this->identificationParcellaire)) {
            $this->dgc = array_keys($this->identificationParcellaire->getDgc())[0];
        }
        else {
            $this->dgc = 'PAS DE DGC';
        }

    }

    protected function parseDocument() {

        // Intitialise les valeurs des clés pour les cepages CDP et DGC
        $synthese = [
            '%%GRENACHE_N' => 0.0,
            '%%SYRAH_N' => 0.0,
            '%%MOURVEDRE_N' => 0.0,
            '%%TIBOUREN_N' => 0.0,
            '%%CINSAULT_N' => 0.0,
            '%%CARIGNAN_N' => 0.0,
            '%%CABERNET_SAUVIGNON_N' => 0.0,
            '%%CALITOR_NOIR_N' => 0.0,
            '%%BARBAROUX_RS' => 0.0,
            '%%VERMENTINO_B' => 0.0,
            '%%UGNI_BLANC_B' => 0.0,
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

        // Intitialise les valeurs des clés pour les cepages DGC
        $synthese_dgc = [
            '%%DGC_GRENACHE_N' => 0.0,
            '%%DGC_SYRAH_N' => 0.0,
            '%%DGC_MOURVEDRE_N' => 0.0,
            '%%DGC_TIBOUREN_N' => 0.0,
            '%%DGC_CINSAULT_N' => 0.0,
            '%%DGC_CARIGNAN_N' => 0.0,
            '%%DGC_CABERNET_SAUVIGNON_N' => 0.0,
            '%%DGC_VERMENTINO_B' => 0.0,
            '%%DGC_UGNI_BLANC_B' => 0.0,
            '%%DGC_CLAIRETTE_B' => 0.0,
            '%%DGC_SEMILLON_B' => 0.0,
        ];

        // TODO : mettre les valeurs DGC

        $this->parse($synthese_dgc);

        $this->parse(['%%DGC' => $this->dgc]);
        
    }

}