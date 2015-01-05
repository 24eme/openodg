<?php

class CompteRechercheForm extends BaseForm 
{
    public function configure() {
        $this->setWidgets(array(
            "query" => new sfWidgetFormInput(),
        ));

        $this->setValidators(array(
            'query' => new sfValidatorString(array("required" => false)),
        ));

        $this->widgetSchema->setNameFormat('compte_recherche[%s]');
    }
}
