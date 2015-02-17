<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ParcellaireAppellationProduitsValidator
 *
 * @author mathurin
 */
class ParcellaireDestinationsValidator extends sfValidatorSchema {

    public function configure($options = array(), $messages = array()) {
        $this->setMessage('required', 'Vous devez séléctionner au moins une destination');
        $this->addMessage('required_acheteurs', 'Vous devez séléctionner au moins un ressortissant pour chacune des destinations cochées');
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
            if(!is_array($destination['acheteurs'])) {
                continue;
            }

            if(count($destination['acheteurs']) == 0) {
                
                throw new sfValidatorErrorSchema($this, array(new sfValidatorError($this, 'required_acheteurs')));
            }
        }

        return $values;
    }

}