<?php

class ValidatorLogin extends sfValidatorBase {
    
    public function configure($options = array(), $messages = array()) {
        $this->setMessage('invalid', "Ce login n'existe pas");
    }

    protected function doClean($values) {
        if (!$values['login']) {
            return array_merge($values);
        }
        
        $etablissement = EtablissementClient::getInstance()->findByIdentifiant($values['login']);

        if (!$etablissement) {
            $etablissement = CompteClient::getInstance()->findByIdentifiant($values['login']);
        }

        if (!$etablissement) {
            throw new sfValidatorErrorSchema($this, array($this->getOption('login') => new sfValidatorError($this, 'invalid')));
        }
            
        return array_merge($values, array('etablissement' => $etablissement));
    }

}