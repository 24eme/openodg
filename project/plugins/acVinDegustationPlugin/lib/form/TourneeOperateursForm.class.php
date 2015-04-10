<?php

class TourneeOperateursForm extends acCouchdbForm {

    public function __construct(acCouchdbDocument $doc, $defaults = array(), $options = array(), $CSRFSecret = null) {
        parent::__construct($doc, $defaults, $options, $CSRFSecret);

        $this->updateDefaults();
    }

    public function updateDefaults() {
        foreach($this->getDocument()->operateurs as $operateur) {
            $lots = $operateur->getLotsPrelevement();

            if(!count($lots)) {
                continue;
            }

            foreach($lots as $lot) {
                $this->setDefault($operateur->getIdentifiant(), $lot->getKey());
                break;
            }
        }
    }

    public function configure() {
        $operateurs = $this->getDocument()->operateurs;

        usort($operateurs, 'DegustationClient::sortOperateursByDatePrelevement');

        foreach($operateurs as $operateur) {
            $choices = array();

            foreach($operateur->lots as $lot_key => $lot) {
                $choices[$lot_key] = sprintf("%s - %s lot(s)", $lot->libelle, $lot->nb);
            }

            $this->setWidget($operateur->identifiant, new sfWidgetFormChoice(array("choices" => $choices)));
            $this->setValidator($operateur->identifiant, new sfValidatorChoice(array("choices" =>array_keys($choices), "required" => false)));

        }

        $this->widgetSchema->setNameFormat('tournee_operateurs[%s]');
    }

    public function update() {
        $values = $this->values;

        $operateursToDelete = array();

        foreach($values as $key => $value) {
            if($key == '_revision') {
                continue;
            }

            if(!$value) {
                $operateursToDelete[] = $key;
                continue;
            }

            $degustation = $this->getDocument()->getDegustationObject($key);
            $degustation->resetLotsPrelevement();
            $lot = $degustation->lots->get($value);
            $lot->prelevement = 1;
            $degustation->updateFromCompte();
        }

        foreach($operateursToDelete as $key) {
           $this->getDocument()->removeDegustation($key); 
        }
    }

}
