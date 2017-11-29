<?php

class CompteRechercheAvanceeForm extends BaseForm 
{
    public function configure() {
        $this->setWidgets(array(
            "cvis" => new sfWidgetFormTextarea(),
        ));

        $this->setValidators(array(
            "cvis" => new sfValidatorString(array("required" => false)),
        ));

        $this->widgetSchema->setNameFormat('recherche_avancee[%s]');
    }
}
