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
            "telephone" => new sfWidgetFormInput(),
            "fax" => new sfWidgetFormInput(),
        ));

        $this->setValidators(array(
            'siret' => new sfValidatorString(array("required" => false)),
            'raison_sociale' => new sfValidatorString(),
            'adresse' => new sfValidatorString(),
            'commune' => new sfValidatorString(),
            'code_postal' => new sfValidatorString(),
            'telephone' => new sfValidatorString(array("required" => false)),
            'fax' => new sfValidatorString(array("required" => false)),
        ));

        $this->widgetSchema->setNameFormat('etablissement[%s]');
    }
}