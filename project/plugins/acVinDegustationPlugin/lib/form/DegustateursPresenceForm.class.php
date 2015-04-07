<?php

class DegustateursPresenceForm extends acCouchdbForm {

    public function __construct(acCouchdbDocument $doc, $defaults = array(), $options = array(), $CSRFSecret = null) {
        parent::__construct($doc, $defaults, $options, $CSRFSecret);

        $this->updateDefaults();
    }

    public function updateDefaults() {
        foreach($this->getDocument()->degustateurs as $degustateursType) {
            foreach($degustateursType as $degustateur) {
                $this->setDefault($degustateur->getHash(), $degustateur->presence);
            }
        }
    }

    public function configure() {
        $choices = array("0" => "", "1" => "");

        foreach($this->getDocument()->degustateurs as $degustateursType) {
            foreach($degustateursType as $degustateur) {
                $this->setWidget($degustateur->getHash(), new sfWidgetFormChoice(array("choices" => $choices, "expanded" => true)));
                $this->setValidator($degustateur->getHash(), new sfValidatorChoice(array("choices" =>array_keys($choices), "required" => false)));
            }
        }

        $this->widgetSchema->setNameFormat('degustateurs_presence[%s]');
    }

    public function update() {
        foreach($this->values as $key => $value) {
            if($key == '_revision') {
                continue;
            }

            $degustateur = $this->getDocument()->get($key);

            if($value === null) {
                $degustateur->presence = null;
            } else {
                $degustateur->presence = (int) $value;
            }
        }
    }

}
