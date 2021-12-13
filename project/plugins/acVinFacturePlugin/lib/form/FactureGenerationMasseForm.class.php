<?php

class FactureGenerationMasseForm extends FactureGenerationForm {


    public function configure() {

        $this->setWidget('regions', new sfWidgetFormChoice(array('choices' => $this->getRegions(), 'multiple' => true, 'expanded' => true, 'default' => array_keys($this->getRegions()))));
        $this->setWidget('seuil', new sfWidgetFormInput(array(), array('autocomplete' => 'off')));
        $this->setWidget('date_mouvement', new sfWidgetFormInput(array('default' => date('d/m/Y')), array('autocomplete' => 'off')));
        $this->setWidget('date_facturation', new sfWidgetFormInput(array('default' => date('d/m/Y')), array('autocomplete' => 'off')));
        $this->setWidget('message_communication', new sfWidgetFormTextarea());
        $this->setWidget('modele', new bsWidgetFormChoice(array('choices' => $this->getModeleChoices(), 'expanded' => true), array("required" => "required")));


        $this->setValidator('regions', new sfValidatorChoice(array('choices' => array_keys($this->getRegions()), 'multiple' => true, 'required' => false)));
	    $this->setValidator('seuil', new sfValidatorNumber(array('required' => false)));
        $this->setValidator('date_mouvement', new sfValidatorString());
        $this->setValidator('date_facturation', new sfValidatorString());
        $this->setValidator('message_communication', new sfValidatorString(array('required' => false)));
        $this->setValidator('modele', new sfValidatorChoice(array('choices' => array_keys($this->getModeleChoices()), 'required' => true)));

        $this->widgetSchema->setLabels(array(
            'regions' => 'Sélectionner des régions à facturer :',
            'seuil_facture' => "Seuil de facturation :",
            'seuil_avoir' => 'Seuil des avoirs :',
            'date_mouvements' => 'Dernière date de prise en compte des mouvements :',
            'date_facturation' => 'Date de facturation :',
            'message_communication' => 'Cadre de communication :',
            "modele" => 'Type de génération :',
        ));
        $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);
        $this->widgetSchema->setNameFormat('facture_generation[%s]');
    }

    public function getRegions() {
        return EtablissementClient::getRegionsWithoutHorsInterLoire();
    }

    public function getModeleChoices() {
        return array(GenerationClient::TYPE_DOCUMENT_FACTURES => 'Facturation', GenerationClient::TYPE_DOCUMENT_EXPORT_COMPTABLE => 'Export comptable', GenerationClient::TYPE_DOCUMENT_EXPORT_XML_SEPA => 'Export XML SEPA');
    }

}
