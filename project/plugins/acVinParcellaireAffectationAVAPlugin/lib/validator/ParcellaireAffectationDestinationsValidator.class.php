<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ParcellaireAffectationAppellationProduitsValidator
 *
 * @author mathurin
 */
class ParcellaireAffectationDestinationsValidator extends sfValidatorSchema {

    public function configure($options = array(), $messages = array()) {
        $this->setMessage('required', 'Vous devez sélectionner au moins une destination');
        $this->addMessage('required_acheteurs', 'Vous devez sélectionner au moins un destinataire pour chacune des cases cochées');
    }

    protected function doClean($values) {

        $errorSchema = new sfValidatorErrorSchema($this);

        $destinations = array();
        foreach($values as $key => $value) {
            if(!is_array($value) || !isset($value['declarant']) || !$value['declarant']) {

                continue;
            }

            $destinations[$key] = $value;
        }

        if(count($destinations) == 0) {

            throw new sfValidatorErrorSchema($this, array(new sfValidatorError($this, 'required')));
        }

        foreach($destinations as $key => $destination) {
            if($key == ParcellaireAffectationClient::DESTINATION_SUR_PLACE) {

                continue;
            }

            if(!is_array($destination['acheteurs']) || !isset($destination['acheteurs']) || count($destination['acheteurs']) == 0) {

                throw new sfValidatorErrorSchema($this, array(new sfValidatorError($this, 'required_acheteurs')));
            }
        }

        return $values;
    }

}