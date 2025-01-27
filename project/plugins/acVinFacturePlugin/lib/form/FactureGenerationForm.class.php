<?php

class FactureGenerationForm extends BaseForm {

    public function __construct($defaults = array(), $options = array(), $CSRFSecret = null) {
        $defaults['date_facturation'] = date('d/m/Y');
        parent::__construct($defaults, $options, $CSRFSecret);
    }


    public function configure()
    {
        $this->setWidget('type_document', new sfWidgetFormChoice(array('choices' => $this->getTypesDocumentFacturant())));
        $this->setWidget('date_mouvement', new sfWidgetFormInput(array('default' => date('d/m/Y')), array('autocomplete' => 'off')));
        $this->setWidget('date_facturation', new sfWidgetFormInput(array('default' => date('d/m/Y')), array('autocomplete' => 'off')));
        $this->setWidget('message_communication', new sfWidgetFormTextarea());

        $this->setValidator('type_document', new sfValidatorChoice(array('choices' => array_keys($this->getTypesDocumentFacturant()), 'required' => true)));
        $this->setValidator('date_facturation' , new sfValidatorDate(array('date_output' => 'Y-m-d', 'date_format' => '~(?P<day>\d{2})/(?P<month>\d{2})/(?P<year>\d{4})~', 'required' => true)));
        $this->setValidator('date_mouvement' , new sfValidatorDate(array('date_output' => 'Y-m-d', 'date_format' => '~(?P<day>\d{2})/(?P<month>\d{2})/(?P<year>\d{4})~', 'required' => true)));
        $this->setValidator('message_communication', new sfValidatorString(array('required' => false)));

        $this->widgetSchema->setLabels(array(
            'type_document' => "Type de document :",
            'message_communication' => 'Cadre de communication :',
            'date_mouvement' => 'Dernière date de prise en compte des mouvements :',
            'date_facturation' => 'Date de facturation :'
        ));
        $this->widgetSchema->setNameFormat('facture_generation[%s]');
    }

    public function getTypesDocumentFacturant() {

        return array_combine(FactureConfiguration::getInstance()->getTypesDocumentFacturant(), FactureConfiguration::getInstance()->getTypesDocumentFacturant());
    }
    public function save() {
        $values = $this->getValues();
        $generation = new Generation();
        if (isset($values['modele'])) {
            $generation->type_document = $values['modele'];
        }else{
            $generation->type_document = GenerationClient::TYPE_DOCUMENT_FACTURES;
        }
        $generation->arguments->add('date_facturation', $values['date_facturation']);
        $generation->arguments->add('date_mouvement', $values['date_mouvement']);
        $generation->arguments->add('type_document', $values['type_document']);
        $generation->arguments->add('message_communication', $values['message_communication']);
        $generation->arguments->add('region', strtoupper(sfConfig::get('app_region', sfConfig::get('sf_app'))));
        return $generation;
    }
}
