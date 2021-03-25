<?php

/**
 * Model for FactureLignes
 *
 */
class FactureLignes extends BaseFactureLignes {

    public function facturerMouvements() {
        foreach ($this as $ligne) {
            $ligne->facturerMouvements();
        }
    }

    public function updateTotaux() {
        foreach ($this as $ligne) {
            $ligne->updateTotaux();
        }
    }

    public function defacturerMouvements() {
        foreach ($this as $ligne) {
            $ligne->defacturerMouvements();
        }
    }

    public function cleanLignes() {
        $lignesToDelete = array();

        $template = $this->getDocument()->getTemplate();
        foreach($this as $ligne) {
            if($template && $template->cotisations->exist($ligne->getKey()) && $template->cotisations->get($ligne->getKey())->isRequired()) {
                continue;
            }

            $ligne->cleanDetails();
            if(!count($ligne->details)) {
                $lignesToDelete[$ligne->getKey()] = $true;
            }
        }

        foreach($lignesToDelete as $key => $void) {
            $this->remove($key);
        }
    }

}