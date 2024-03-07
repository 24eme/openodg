<?php
/**
 * Model for FichierDonnee
 *
 */

class FichierDonnee extends BaseFichierDonnee {

    public function updateTiers() {
        if (strpos('ETABLISSEMENT-', $this->tiers) !== null) {
            $e = EtablissementClient::getInstance()->find($this->tiers);
            $this->tiers_cvi = $e->cvi;
            $this->tiers_raison_sociale = $e->raison_sociale;
            $this->tiers_commune = $e->commune;
        }
    }

}
