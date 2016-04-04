<?php

class FacturationMassiveForm extends acCouchdbForm {

    public function configure() {
        $modeles = $this->getModeles();
        $this->setWidgets(array(
                'requete'   => new sfWidgetFormInput(),
                'modele'   => new sfWidgetFormChoice(array('choices' => $modeles)),
                'date_facturation'   => new sfWidgetFormInput(array('default' => date('d/m/Y'))),
                'libelle'   => new sfWidgetFormInput(),
        ));

        $this->widgetSchema->setLabels(array(
                'requete'  => 'Reqûete',
                'modele'  => 'Template de facture',
                'date_facturation'  => 'Date de facturation',
                'libelle'  => 'Libellé',
        ));

        $this->setValidators(array(
                'requete' => new sfValidatorString(array("required" => true)),
                'modele' => new sfValidatorChoice(array('choices' => array_keys($modeles), 'multiple' => false, 'required' => true)),
                'date_facturation' => new sfValidatorDate(array('date_output' => 'Y-m-d', 'date_format' => '~(?P<day>\d{2})/(?P<month>\d{2})/(?P<year>\d{4})~', 'required' => true)),
                'libelle' => new sfValidatorString(array("required" => true)),
        ));

        $this->widgetSchema->setNameFormat('facturation_massive[%s]');
    }

    public function updateDocument() {
        $this->getDocument()->libelle = $this->getValue('libelle');
        $this->getDocument()->arguments->add('requete', $this->getValue('requete'));
        $this->getDocument()->arguments->add('modele', $this->getValue('modele'));
        $this->getDocument()->arguments->add('date_facturation', $this->getValue('date_facturation'));
    }

    public function getModeles() {

        return self::getModelesByObject($this->getOption("modeles"));
    }

    public static function getModelesByObject($modeles) {
        $choices = array("" => "");

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
