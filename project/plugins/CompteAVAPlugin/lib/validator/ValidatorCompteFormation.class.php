<?php

class ValidatorCompteFormation extends sfValidatorBase 
{

    public function configure($options = array(), $messages = array()) 
    {
    }

    protected function doClean($values) 
    {

        if(!isset($values['produit_hash']) && !isset($values['heures']) && !isset($values['annee'])) {

            return array();
        }

        if(!isset($values['produit_hash'])) {
        
            throw new sfValidatorErrorSchema($this, array('produit_hash' => new sfValidatorError($this, 'required')));
        }

        if(!isset($values['annee'])) {
            
            throw new sfValidatorErrorSchema($this, array('annee' => new sfValidatorError($this, 'required')));
        }

        if(!isset($values['heures'])) {
            
            throw new sfValidatorErrorSchema($this, array('heures' => new sfValidatorError($this, 'required')));
        }

        return $values;
    }
}