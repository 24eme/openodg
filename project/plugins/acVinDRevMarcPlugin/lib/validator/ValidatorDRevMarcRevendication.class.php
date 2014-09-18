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
        $this->addMessage('invalid_volume_marc', 'La volume d\'alcool pur semble être exprimé en hl');
    }

    protected function doClean($values) {

        $errorSchema = new sfValidatorErrorSchema($this);
        
        if ($values['volume_obtenu'] > $values['qte_marc'] * 20 / 10000) {
            $errorSchema->addError(new sfValidatorError($this, 'invalid_volume_marc'), 'volume_obtenu');
        }
        if (count($errorSchema)) {
            throw new sfValidatorErrorSchema($this, $errorSchema);
        }

        return $values;
    }

}
