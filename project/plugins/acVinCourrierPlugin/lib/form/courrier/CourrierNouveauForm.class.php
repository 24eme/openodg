<?php
class CourrierNouveauForm extends BaseForm {

    public function __construct(Etablissement $etablissement, $lot, $defaults = array(), $options = array(), $CSRFSecret = null)
    {
        $this->etablissement = $etablissement;
        $this->lot = $lot;
        $defaults['lot_unique_id'] = $lot->unique_id;
        parent::__construct($defaults, $options, $CSRFSecret);
    }



    public function configure() {

        $this->setWidget('courrier_type', new bsWidgetFormChoice(array('choices' => $this->getCourrierChoices())));
        $this->setValidator('courrier_type', new sfValidatorPass(array('required' => true)));

        $this->widgetSchema->setNameFormat('courrier_creation[%s]');

    }

    public static function getCourrierChoices() {
        $choices = CourrierClient::getInstance()->getTitres();
        $choices[''] = '';
        ksort($choices);
        return $choices;
    }

    public function getLot() {
        return $this->lot;
    }

    public function save() {

        $values = $this->getValues();
        $courrier = CourrierClient::getInstance()->createDoc($this->etablissement->identifiant, $values['courrier_type'], $this->lot);
        $courrier->save();
        return $courrier;

    }

}
