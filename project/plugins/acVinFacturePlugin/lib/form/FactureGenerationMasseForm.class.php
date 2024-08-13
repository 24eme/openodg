<?php

class FactureGenerationMasseForm extends FactureGenerationForm {


    public function configure() {

        $this->setWidget('seuil', new sfWidgetFormInput(array(), array('autocomplete' => 'off')));
        $this->setWidget('date_mouvement', new sfWidgetFormInput(array('default' => date('d/m/Y')), array('autocomplete' => 'off')));
        $this->setWidget('date_facturation', new sfWidgetFormInput(array('default' => date('d/m/Y')), array('autocomplete' => 'off')));
        $this->setWidget('message_communication', new sfWidgetFormTextarea());
        $this->setWidget('modele', new bsWidgetFormChoice(array('choices' => $this->getModeleChoices(), 'expanded' => true), array("required" => "required")));
        $this->setWidget('type_document', new sfWidgetFormChoice(array('choices' => $this->getTypesDocumentFacturant())));

	    $this->setValidator('seuil', new sfValidatorNumber(array('required' => false)));
        $this->setValidator('date_mouvement', new sfValidatorString());
        $this->setValidator('date_facturation', new sfValidatorString());
        $this->setValidator('message_communication', new sfValidatorString(array('required' => false)));
        $this->setValidator('modele', new sfValidatorChoice(array('choices' => array_keys($this->getModeleChoices()), 'required' => true)));
        $this->setValidator('type_document', new sfValidatorChoice(array('choices' => array_keys($this->getTypesDocumentFacturant()), 'required' => true)));

        $this->widgetSchema->setLabels(array(
            'seuil_facture' => "Seuil de facturation :",
            'seuil_avoir' => 'Seuil des avoirs :',
            'date_mouvements' => 'Dernière date de prise en compte des mouvements :',
            'date_facturation' => 'Date de facturation :',
            'message_communication' => 'Cadre de communication :',
            "modele" => 'Type de génération :',
            "type_document" => 'Type de document :',
        ));

        if(!FactureConfiguration::getInstance()->displayTypesDocumentOnMassive()) {
            unset($this['type_document']);
        }

        $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);
        $this->widgetSchema->setNameFormat('facture_generation[%s]');
    }

    public function getRegions() {
        $r = Organisme::getCurrentOrganisme();
        return array($r => $r);
    }

    public function getModeleChoices() {
        return array(
            GenerationClient::TYPE_DOCUMENT_FACTURES => 'Facturation',
            GenerationClient::TYPE_DOCUMENT_EXPORT_COMPTABLE => 'Export comptable',
            GenerationClient::TYPE_DOCUMENT_EXPORT_XML_SEPA => 'Export XML SEPA'
        );
    }

}
