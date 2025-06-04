<?php

class FactureGenerationMasseForm extends FactureGenerationForm {


    public function configure() {
        parent::configure();

        $this->setWidget('modele', new bsWidgetFormChoice(array('choices' => $this->getModeleChoices(), 'expanded' => true), array("required" => "required")));

        $this->setValidator('modele', new sfValidatorChoice(array('choices' => array_keys($this->getModeleChoices()), 'required' => true)));

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
