<?php

class CompteChoiceCreationForm extends sfForm {

    public function configure() {
        $this->setWidget("type_compte", new sfWidgetFormChoice(array('choices' => $this->getTypesCompte())));
        $this->setValidator("type_compte", new sfValidatorChoice(array('required' => true, 'choices' => array_keys($this->getTypesCompte())), array('required' => "Aucun type de compte n'a été choisie.")));
        $this->widgetSchema->setNameFormat('compte_creation[%s]');
    }

    public function getTypesCompte() {
        return CompteClient::getInstance()->getAllTypesCompte();
    }

}
