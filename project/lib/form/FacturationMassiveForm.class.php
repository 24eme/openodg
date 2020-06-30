<?php

class FacturationMassiveForm extends acCouchdbForm {

    public function configure() {
        $this->setWidget('date_facturation'   , new sfWidgetFormInput(array('default' => date('d/m/Y'))));
        $this->widgetSchema->setLabel('date_facturation'  , 'Date de facturation');
        $this->setValidator('date_facturation' , new sfValidatorDate(array('date_output' => 'Y-m-d', 'date_format' => '~(?P<day>\d{2})/(?P<month>\d{2})/(?P<year>\d{4})~', 'required' => true)));

        if(!$this->getOption("uniqueTemplateFactureName")){
          $modeles = $this->getModeles();
          $this->setWidget('modele'   , new sfWidgetFormChoice(array('choices' => $modeles)));
          $this->setWidget('libelle'   , new sfWidgetFormInput());
          $this->setWidget('requete'   , new sfWidgetFormInput());
          $this->widgetSchema->setLabel('modele'  , 'Template de facture');
          $this->widgetSchema->setLabel('libelle'  , 'Libellé');
          $this->widgetSchema->setLabel('requete'  , 'Requête');
          $this->setValidator('requete' , new sfValidatorString(array("required" => true)));
          $this->setValidator('modele' , new sfValidatorChoice(array('choices' => array_keys($modeles), 'multiple' => false, 'required' => true)));
          $this->setValidator('libelle' , new sfValidatorString(array("required" => true)));
        }
        $this->widgetSchema->setNameFormat('facturation_massive[%s]');
    }

    public function updateDocument() {
        $this->getDocument()->libelle = $this->getValue('libelle');
        $this->getDocument()->arguments->add('requete', $this->getValue('requete'));
        if(!$this->getOption("uniqueTemplateFactureName")){
          $this->getDocument()->arguments->add('modele', $this->getValue('modele'));
        }else{
          $this->getDocument()->arguments->add('modele', $this->getOption("uniqueTemplateFactureName"));
        }
        $this->getDocument()->arguments->add('date_facturation', $this->getValue('date_facturation'));
    }

    public function getModeles() {

        return self::getModelesByObject($this->getOption("modeles"));
    }

    public static function getModelesByObject($modeles) {
        $choices = [];

        if ($modeles === null) {
            $modeles = [];
        }

        foreach ($modeles as $templateFacture) {
            $choices[$templateFacture->_id] = $templateFacture->libelle;
        }

        uasort($choices, "FacturationMassiveForm::sortModeles");

        return $choices;
    }

    public static function sortModeles($a, $b) {
        if($a == "") {

            return false;
        }

        if($b == "") {

            return true;
        }

        return $a < $b;
    }

}
