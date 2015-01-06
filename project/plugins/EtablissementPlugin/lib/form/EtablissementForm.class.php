<?php

class EtablissementForm extends CompteModificationForm
{
    public function __construct(\acCouchdbJson $object, $options = array(), $CSRFSecret = null) {
        parent::__construct($object, $options, $CSRFSecret);
    }
    
     public function configure() {
       $this->setWidgets(array(
            "siret" => new sfWidgetFormInput(array("label" => "N° SIRET")),
            "raison_sociale" => new sfWidgetFormInput(array("label" => "Raison Sociale")),
            "adresse" => new sfWidgetFormInput(array("label" => "Adresse")),
            "commune" => new sfWidgetFormInput(array("label" => "Commune")),
            "code_postal" => new sfWidgetFormInput(array("label" => "Code Postal")),
            "telephone_bureau" => new sfWidgetFormInput(array("label" => "Tél. Bureau")),
            "telephone_mobile" => new sfWidgetFormInput(array("label" => "Tél. Mobile")),
            "telephone_prive" => new sfWidgetFormInput(array("label" => "Tél. Privé")),
            "fax" => new sfWidgetFormInput(array("label" => "Fax")),
            "email" => new sfWidgetFormInput(array("label" => "Email")),
        ));

        $this->setValidators(array(
            'siret' => new sfValidatorRegex(array("required" => false, "pattern" => "/^[0-9]{14}$/"), array("invalid" => "Le siret doit être un nombre à 14 chiffres")),
            'raison_sociale' => new sfValidatorString(array("required" => true)),
            'adresse' => new sfValidatorString(array("required" => true)),
            'commune' => new sfValidatorString(array("required" => true)),
            'code_postal' => new sfValidatorString(array("required" => true)),
            'telephone_bureau' => new sfValidatorString(array("required" => false)),
            'telephone_mobile' => new sfValidatorString(array("required" => false)),
            'telephone_prive' => new sfValidatorString(array("required" => false)),
            'fax' => new sfValidatorString(array("required" => false)),
       	    'email' => new sfValidatorEmailStrict(array("required" => true)),
        )); 

        if(!$this->getOption("use_email")) {
            $this->getValidator('email')->setOption('required', false);
        }

        if($this->getObject()->exist('siren') && $this->getObject()->identifiant == $this->getObject()->siren) {
            unset($this['siret']);
        }

        $this->widgetSchema->setNameFormat('etablissement[%s]');
    }
}