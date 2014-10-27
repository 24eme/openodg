<?php

class EtablissementForm extends acCouchdbObjectForm
{
     public function configure() {
       $this->setWidgets(array(
            "siret" => new sfWidgetFormInput(),
            "raison_sociale" => new sfWidgetFormInput(),
            "adresse" => new sfWidgetFormInput(),
            "commune" => new sfWidgetFormInput(),
            "code_postal" => new sfWidgetFormInput(),
            "telephone_bureau" => new sfWidgetFormInput(),
            "telephone_mobile" => new sfWidgetFormInput(),
            "telephone_prive" => new sfWidgetFormInput(),
            "fax" => new sfWidgetFormInput(),
       		"email" => new sfWidgetFormInput(),
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
       		"email" => new sfValidatorEmailStrict(array("required" => true)),
        ));

        $this->widgetSchema->setNameFormat('etablissement[%s]');
    }
}