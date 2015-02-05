    <?php

class ValidatorLogin extends sfValidatorBase {
    
    public function configure($options = array(), $messages = array()) {
        $this->setMessage('invalid', "Ce login n'existe pas");
    }

    protected function doClean($values) {
        if (!$values['login']) {
            return array_merge($values);
        }
        
        $compte = CompteClient::getInstance()->find($values['login']);

        if (!$compte) {
            throw new sfValidatorErrorSchema($this, array($this->getOption('login') => new sfValidatorError($this, 'invalid')));
        }

        $etablissement = $compte->getEtablissementObj();

        if (!$etablissement) {
            return array_merge($values, array('compte' => $compte));
        }

        return array_merge($values, array('compte' => $compte, 'etablissement' => $etablissement));
    }

}