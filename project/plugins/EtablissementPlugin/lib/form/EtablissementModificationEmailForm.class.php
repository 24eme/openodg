<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of EtablissementModificationEmailForm
 *
 * @author mathurin
 */
class EtablissementModificationEmailForm extends acCouchdbObjectForm
{
     public function configure() {
       $this->setWidgets(array(
            "email" => new sfWidgetFormInput(),
        ));

        $this->setValidators(array(
            'email' => new sfValidatorEmail(array("required" => false), array("invalid" => "L'email doit être valide")),
            
        ));

        $this->widgetSchema->setNameFormat('etablissement_email[%s]');
    }

    protected function doUpdateObject($values) 
    {
        parent::doUpdateObject($values);
        $this->getObject()->updateCompte();
    }
}