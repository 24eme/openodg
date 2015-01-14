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
class CompteChaiNouveauForm extends sfForm {

    public function configure() {

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
            'adresse' => new sfValidatorString(array('required' => false, 'min_length' => 3)),
            'commune' => new sfValidatorString(array('required' => false, 'min_length' => 2)),
            'code_postal' => new sfValidatorString(array('required' => false, 'min_length' => 2))));
        $this->widgetSchema->setNameFormat('comptechais[%s]');
    }

    public function doUpdateObject($values) {
        parent::doUpdateObject($values);
    }

}
