<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of CompteChaiNouveauForm
 *
 * @author mathurin
 */
class CompteChaiNouveauForm extends acCouchdbObjectForm {

    public function configure() {

//        $siretCni_errors = array('required' => 'SIRET obligatoire ou CNI le cas échéant', 'invalid' => 'Le Siret/Cni doit être soit un numéro de siret (14 chiffres) soit un numéro Cni (12 chiffres ou lettres majuscules)');
//        $cniValid = new ValidatorCni();
//        $siretValid = new ValidatorSiret();
        $this->setWidgets(array(
            'adresse' => new sfWidgetFormInputText(),
            'commune' => new sfWidgetFormInputText(),
            'code_postal' => new sfWidgetFormInputText()
        ));
        $this->widgetSchema->setLabels(array(
            'adresse' => 'Adresse',
            'commune' => 'Commune',
            'code_postal' => 'Code postal'
        ));
        $this->setValidators(array(
            'adresse' => new sfValidatorString(array('required' => true, 'min_length' => 3)),
            'commune' => new sfValidatorString(array('required' => true, 'min_length' => 2)),
            'code_postal' => new sfValidatorString(array('required' => true, 'min_length' => 2))));
        $this->widgetSchema->setNameFormat('comptechais[%s]');
//        $this->mergePostValidator(new ValidatorEtablissementSiretCni());
    }

    public function doUpdateObject($values) {
        parent::doUpdateObject($values);
    }

}
