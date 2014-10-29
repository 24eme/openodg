<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of EtablissementConfirmationEmailForm
 *
 */
class EtablissementConfirmationEmailForm extends acCouchdbObjectForm
{
     public function configure() 
     {
       $this->setWidgets(array(
            "email" => new sfWidgetFormInput(),
        ));

        $this->setValidators(array(
            'email' => new sfValidatorEmailStrict(array("required" => true), array("invalid" => "L'email doit être valide")),
            
        ));

        $this->widgetSchema->setNameFormat('etablissement_confirmation_email[%s]');
    }

    protected function doUpdateObject($values) 
    {
        parent::doUpdateObject($values);
        if ($this->getObject()->needEmailConfirmation()) {
        	$this->getObject()->add('date_premiere_connexion', date('Y-m-d H:i:s'));
        }
    }
}