<?php

class ParcellaireIntentionAuto extends ParcellaireIntentionAffectation {
    public function save() {
        throw new sfException('Cannont save ParcellaireIntentionAuto');
    }

    public function updateParcelles() {
        $this->addParcellesFromParcellaire(["DEFAUT"]);
    }

    public function getDenominationAire() {
        return "Ventoux";
    }

}
