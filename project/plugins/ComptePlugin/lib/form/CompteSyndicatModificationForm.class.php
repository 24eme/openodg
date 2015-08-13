<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of CompteDegustateurModificationForm
 *
 * @author mathurin
 */
class CompteSyndicatModificationForm extends CompteModificationForm {

   
    public function __construct(\acCouchdbJson $object, $options = array(), $CSRFSecret = null) {
        parent::__construct($object, $options, $CSRFSecret);
    }

    public function configure() {
        parent::configure();

        $this->setWidget("raison_sociale", new sfWidgetFormInput(array("label" => "Raison sociale")));
        $this->setValidator('raison_sociale', new sfValidatorString(array("required" => true)));        
    }

    public function save($con = null) {
        parent::save($con);
    }

}
