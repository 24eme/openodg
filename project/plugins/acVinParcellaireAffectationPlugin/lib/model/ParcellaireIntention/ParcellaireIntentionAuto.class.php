<?php

class ParcellaireIntentionAuto extends ParcellaireIntentionAffectation {

    public function save() {
        throw new sfException('Cannont save ParcellaireIntentionAuto');
    }

}
