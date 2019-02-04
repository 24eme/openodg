<?php

class FacturationDeclarantForm extends BaseForm {

    public function configure() {

        $this->setWidget('date_facturation', new sfWidgetFormInput(array('default' => date('d/m/Y'))));
        $this->widgetSchema->setLabel('date_facturation', 'Date de facturation');
        $this->setValidator('date_facturation',new sfValidatorDate(array('date_output' => 'Y-m-d', 'date_format' => '~(?P<day>\d{2})/(?P<month>\d{2})/(?P<year>\d{4})~', 'required' => true)));

        if(!$this->getOption("uniqueTemplateFactureName")){
          $choices = $this->getModeles();
          $this->setWidget('modele', new sfWidgetFormChoice(array('choices' => $choices)));
          $this->widgetSchema->setLabel('modele', 'Template de facture');
          $this->setValidator('modele', new sfValidatorChoice(array('choices' => array_keys($choices), 'multiple' => false, 'required' => true)));
        }
        $this->widgetSchema->setNameFormat('facturation_declarant[%s]');
    }

	public function getModeles() {
        return FacturationMassiveForm::getModelesByObject($this->getOption("modeles"));
    }

}
