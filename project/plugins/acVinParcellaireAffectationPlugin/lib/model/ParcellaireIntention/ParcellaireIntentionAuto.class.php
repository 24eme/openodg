<?php

class ParcellaireIntentionAuto extends ParcellaireIntentionAffectation {
    public function save() {
        throw new sfException('Cannont save ParcellaireIntentionAuto');
    }

    public function updateParcelles() {
        $this->updateIntentionFromParcellaireAndLieux(["DEFAUT"]);
    }

    public function getDenominationAire() {
        return "Ventoux";
    }

}
