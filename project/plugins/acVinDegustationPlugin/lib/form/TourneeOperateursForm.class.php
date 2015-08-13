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

            $defaultLots = array();
            foreach($lots as $lot) {
                $defaultLots[] = $lot->getKey();
            }

            $this->setDefault($operateur->getIdentifiant(), $defaultLots);
        }
    }

    public function configure() {
        $operateurs = $this->getDocument()->operateurs;

        usort($operateurs, 'DegustationClient::sortOperateursByDatePrelevement');

        foreach($operateurs as $operateur) {
            $choices = array();

            foreach($operateur->lots as $lot_key => $lot) {
                $choices[$lot_key] = sprintf("%s - %s hl", $lot->libelle, $lot->volume_revendique);

                if($lot->nb > 1) {
                    $choices[$lot_key] .= sprintf(" (%s lots)", $lot->nb);
                }
            }

            $this->setWidget($operateur->identifiant, new sfWidgetFormChoice(array("choices" => $choices, 'multiple' => true)));
            $this->setValidator($operateur->identifiant, new sfValidatorChoice(array("choices" =>array_keys($choices), "multiple" => true, "required" => false)));

        }

        $this->widgetSchema->setNameFormat('tournee_operateurs[%s]');
    }

    public function update() {
        $values = $this->values;

        $operateursToKeep = array();

        foreach($values as $key => $value) {
            if($key == '_revision') {
                continue;
            }

            if(!$value || !count($value)) {
                continue;
            }

            $degustation = $this->getDocument()->getDegustationObject($key);
            $degustation->resetLotsPrelevement();
            foreach($value as $hash) {
                $lot = $degustation->lots->get($hash);
                $lot->prelevement = 1;
            }
            $degustation->updateFromCompte();

            $operateursToKeep[$key] = true;
        }

        $operateursToDelete = array();
        foreach($this->getDocument()->getDegustationsObject() as $key => $degustation) {
            if(array_key_exists($key, $operateursToKeep)) {
                continue;    
            }

            if($degustation->isAffecteTournee()) {
                continue;
            }
            
            $operateursToDelete[] = $key;
            
        }

        foreach($operateursToDelete as $key) {
            $this->getDocument()->removeDegustation($key);
        }
    }

}
