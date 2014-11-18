<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ValidatorDRevMarcRevendication
 *
 * @author mathurin
 */
class ValidatorDRevMarcRevendication extends sfValidatorBase {

    public function configure($options = array(), $messages = array()) {
        $this->addMessage('invalid_volume_marc', 'Le volume d\'alcool pur semble être exprimé en litre');
        $this->addMessage('invalid_periode_distillation', 'La date de début de distillation doit être antérieure à la date de fin de distillation');
    
        
    }

    protected function doClean($values) {

        $errorSchema = new sfValidatorErrorSchema($this);
        
        if ($values['volume_obtenu'] > $values['qte_marc'] * 20 / 10000) {
            $errorSchema->addError(new sfValidatorError($this, 'invalid_volume_marc'), 'volume_obtenu');
        }        
        
        $date_debut_distillation = Date::getIsoDateFromFrenchDate($values['debut_distillation']);
        $date_fin_distillation = Date::getIsoDateFromFrenchDate($values['fin_distillation']);      
        
        
        if ($date_debut_distillation > $date_fin_distillation) {
            $errorSchema->addError(new sfValidatorError($this, 'invalid_periode_distillation'), 'debut_distillation');
        }
        
        
        if (count($errorSchema)) {
            throw new sfValidatorErrorSchema($this, $errorSchema);
        }

        return $values;
    }

}
