<?php

class DegustationOperateursForm extends acCouchdbForm {

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
                $this->setDefault($operateur->getKey(), $lot->getKey());
                break;
            }
        }
    }

    public function configure() {
        $operateurs = $this->getDocument()->operateurs->toArray();

        usort($operateurs, 'DegustationOperateursForm::sortOperateursByDatePrelevement');

        foreach($operateurs as $operateur) {
            $choices = array();

            foreach($operateur->lots as $lot_key => $lot) {
                $choices[$lot_key] = sprintf("%s - %s lot(s)", $lot->libelle, $lot->nb);
            }

            $this->setWidget($operateur->getKey(), new sfWidgetFormChoice(array("choices" => $choices)));
            $this->setValidator($operateur->getKey(), new sfValidatorChoice(array("choices" =>array_keys($choices), "required" => false)));

        }

        $this->widgetSchema->setNameFormat('operateurs[%s]');
    }

    public static function sortOperateursByDatePrelevement($operateur_a, $operateur_b) {

        return $operateur_a->date_demande > $operateur_b->date_demande;
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
            $operateur = $this->getDocument()->operateurs->get($key);
            $operateur->resetLotsPrelevement();
            $lot = $operateur->lots->get($value);
            $lot->prelevement = 1;
            $operateur->consoliderInfos();
        }

        foreach($operateursToDelete as $key) {
           $this->getDocument()->operateurs->remove($key); 
        }
    }

}
