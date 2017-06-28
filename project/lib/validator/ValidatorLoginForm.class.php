<?php

class ValidatorLogin extends sfValidatorBase {

    public function configure($options = array(), $messages = array()) {
        $this->setMessage('invalid', "Ce login n'existe pas");
    }

    protected function doClean($values) {
        if (!$values['login']) {
            return array_merge($values);
        }

        $id = $values['login'];

        if(!preg_match("/COMPTE-/",  $values['login'])) {
            $id = "COMPTE-". $values['login'];
        }

        $compte = CompteClient::getInstance()->find($id);

        if(!$compte) {
            if(!preg_match("/COMPTE-/",  $values['login'])) {
                $id = "COMPTE-E". $values['login'];
            }
            $compte = CompteClient::getInstance()->find($id);
        }

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
