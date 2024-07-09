<?php

/**
 * Crée le fichier ODS de qui calcule les potentiels de production
 */
class ExportParcellairePotentielProductionODS extends ExportCalculPPODS {

    private $identificationParcellaire;
    private $etablissement;

    public function __construct($parcellaire) {
        $this->etablissement = $parcellaire->getEtablissementObject();
        $this->identificationParcellaire = ParcellaireAffectationClient::getInstance()->getLast($this->etablissement->identifiant);
        if (!$this->identificationParcellaire) {
            $this->identificationParcellaire = ParcellaireIntentionClient::getInstance()->getLast($this->etablissement->identifiant);
        }
        if(isset($this->identificationParcellaire)) {
            $dgc = array_keys($this->identificationParcellaire->getDgc())[0];
        }
        else {
            $dgc = 'CDP';
        }

        // Rempli le tableau avec les superficie des cepages qu'on a
        foreach ($parcellaire->getParcelles() as $p) {
            if (ParcellaireConfiguration::getInstance()->isJeunesVignesEnabled() && !$p->hasJeunesVignes()) {
                continue;
            }

            $key = $p->getCepage();

            if (!isset($cepages[$key])) {
                $cepages[$key] = 0;
            }
            $cepages[$key] = round($cepages[$key] + $p->superficie, 6);
        }
        parent::__construct($dgc, $cepages);
        $this->parcellaire = $parcellaire;
    }
}