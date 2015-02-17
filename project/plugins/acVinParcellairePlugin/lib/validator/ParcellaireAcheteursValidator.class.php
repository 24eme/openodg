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
class ParcellaireAcheteursValidator extends sfValidatorSchema {

    public function configure($options = array(), $messages = array()) {
        $this->setMessage('required', 'Vous devez séléctionner une destination par produit');
    }

    protected function doClean($values) {

        $errorSchema = new sfValidatorErrorSchema($this);

        foreach($values as $key => $value) {
            if($key == '_revision') {
                continue;
            }

            if(count($value) == 0) {
                
                throw new sfValidatorErrorSchema($this, array(new sfValidatorError($this, 'required')));
            }
        }

        return $values;
    }

}
