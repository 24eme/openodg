<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of DRMLegalSignatureForm
 *
 * @author tangui
 */
class DRevLegalSignatureForm extends BaseForm {

    protected $etablissement = null;

    public function __construct($etablissement, $options = array(), $CSRFSecret = null) {
        $this->etablissement = $etablissement;
        parent::__construct($options, $CSRFSecret);
    }

    public function configure() {
        $this->setWidgets(array(
            'terms' => new sfWidgetFormInputCheckbox()
        ));
        $this->setValidators(array('terms' => new sfValidatorPass(array('required' => true))));

        $this->widgetSchema->setNameFormat('drev_legal_signature[%s]');
    }


    public function save() {
        if ($this->getValue('terms') && $this->etablissement) {
            $societe = $this->etablissement->getSociete();
            if($societe){
              $societe->setLegalSignatureDrev();
              $societe->save();
            }
        }
    }

}
