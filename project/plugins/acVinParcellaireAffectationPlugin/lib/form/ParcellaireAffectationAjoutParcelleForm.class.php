<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ParcellaireAjoutParcelleForm
 *
 * @author mathurin
 */
class ParcellaireAffectationAjoutParcelleForm extends ParcellaireAffectationParcelleForm {

    public function __construct(acCouchdbJson $object, $options = array(), $CSRFSecret = null) {
        parent::__construct($object, $options, $CSRFSecret);
    }

    public function configure() {
        parent::configure();

        $this->widgetSchema->setNameFormat('parcellaire_ajout_parcelle[%s]');
    }

    protected function updateDefaultsFromObject() {
        parent::updateDefaultsFromObject();
    }


}
