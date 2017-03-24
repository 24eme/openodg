<?php

class TourneeCreationForm extends acCouchdbObjectForm
{
    public function configure() {
        $this->setWidget('date_prelevement_debut', new sfWidgetFormInput(array(), array()));
        $this->setValidator('date_prelevement_debut', new sfValidatorDate(array('date_output' => 'Y-m-d', 'date_format' => '~(?P<day>\d{2})/(?P<month>\d{2})/(?P<year>\d{4})~', 'required' => false)));

        $this->setWidget('date', new sfWidgetFormInput(array(), array()));
        $this->setValidator('date', new sfValidatorDate(array('date_output' => 'Y-m-d', 'date_format' => '~(?P<day>\d{2})/(?P<month>\d{2})/(?P<year>\d{4})~', 'required' => true)));

        $this->setWidget('appellation', new sfWidgetFormChoice(array('choices' => $this->getAppellationChoices())));
        $this->setValidator('appellation', new sfValidatorChoice(array('choices' => array_keys($this->getAppellationChoices()))));

        $this->setWidget('action', new sfWidgetFormChoice(array('choices' => $this->getActionChoices())));
        $this->setValidator('action', new sfValidatorChoice(array('choices' => array_keys($this->getActionChoices()))));


        $this->widgetSchema->setNameFormat('tournee_creation[%s]');
    }

    public static function getActionChoices() {

        return array("organiser" => "Organiser une tournée", "saisir" => " Saisir une dégusation");
    }

    public static function getAppellationChoices() {

        return array(
            "" => "",
            "ALSACE" => "AOC Alsace",
            "VTSGN" => "VT / SGN",
            "GRDCRU" => "Grands crus"
        );
    }

    protected function updateDefaultsFromObject() {
        parent::updateDefaultsFromObject();
        if($this->getObject()->date_prelevement_debut) {
            $datePrelevementDebut = new DateTime($this->getObject()->date_prelevement_debut);
            $this->setDefault('date_prelevement_debut', $datePrelevementDebut->format('d/m/Y'));
        }
        if($this->getObject()->date) {
            $date = new DateTime($this->getObject()->date);
            $this->setDefault('date', $date->format('d/m/Y'));
        }

        $this->setDefault("action", "organiser");
    }

     public function doUpdateObject($values)
    {
        parent::doUpdateObject($values);
        $appellationsWithLibelle = self::getAppellationChoices();
        $appellation = $values["appellation"];
        $this->getObject()->appellation_libelle = $appellationsWithLibelle[$appellation];
    }
}
