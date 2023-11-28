<?php
class CourrierExtrasNouveauForm extends BaseForm {

    public function __construct(Etablissement $etablissement, $lot, $defaults = array(), $options = array(), $CSRFSecret = null)
    {
        $this->etablissement = $etablissement;
        $this->lot = $lot;
        $defaults['lot_unique_id'] = $lot->unique_id;
        parent::__construct($defaults, $options, $CSRFSecret);
    }


    public function configure() {
        $this->setWidgets(array(
            'agent_nom'    => new sfWidgetFormInput(array('label' => 'Nom de l\'agent')),
            'representant_nom' => new sfWidgetFormInput(array('label' => 'Nom du représentant')),
            'représentant_fonction' => new sfWidgetFormInput(array('label' => 'Fonction du représentant')),

        ));
        $this->setValidators(array(
            'agent_nom' => new sfValidatorString(),
            'representant_nom' => new sfValidatorString,
            'représentant_fonction' => new sfValidatorString,
        ));


        $this->widgetSchema->setNameFormat('courrier_creation[%s]');

    }

    public function save() {

        $values = $this->getValues();
        $courrier = CourrierClient::getInstance()->createDoc($this->etablissement->identifiant, $values['courrier_type'], $this->lot);
        $courrier->save();
        return $courrier;

    }

}
