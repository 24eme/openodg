<?php

class ValidatorPhone extends sfValidatorString {
    protected function configure($options = array(), $messages = array())
    {
        parent::configure($options, $messages);
        $this->setMessage('invalid', "Le numéro de téléphone n'est pas correct. Il doit respecter les formats suivants : 0101010101, 01 01 01 01 01, 01.01.01.01.01, +33101010101");
    }

    protected function doClean($value)
    {
        $clean = parent::doClean($value);

        $value = preg_replace("/[-\. ]+/", "", $value);
          
        if(preg_match("/^\+[0-9]{2}[0-9]{9}$/", $value)) {

            return $clean;
        }

        if(preg_match("/^[0-9]{10}$/", $value)) {

            return $clean;
        }

        throw new sfValidatorError($this, 'invalid', array('value' => $value));
    }
}

