<?php

class LoginForm extends BaseForm {

    /**
     * 
     */
    public function configure() {

        $choices = $this->getChoices();
        
        $this->setWidgets(array(
                'login'   => new sfWidgetFormChoice(array("choices" => $choices)),
        ));

        $this->widgetSchema->setLabels(array(
                'login'  => 'Login : ',
        ));

        $this->setValidators(array(
                'login' => new sfValidatorChoice(array("required" => true, "choices" => array_keys($choices)))
        ));
        
        $this->widgetSchema->setNameFormat('login[%s]');

        $this->validatorSchema['login']->setMessage('required', 'Champs obligatoire');
        $this->validatorSchema->setPostValidator(new ValidatorLogin());
    }

    public function getChoices() {
        $etablissements = EtablissementClient::getInstance()->getAll()->getDocs();
        $choices = array("" => "");
        foreach($etablissements as $etablissement) {
            $choices[$etablissement["identifiant"]] = sprintf("%s - %s %s - %s (%s)", 
                $etablissement["raison_sociale"], 
                $etablissement["code_postal"], 
                $etablissement["commune"], 
                $etablissement["identifiant"],
                implode(", ", array_keys($etablissement["familles"])));
        }

        if($this->getOption("use_compte")) {
            $comptes = CompteClient::getInstance()->getAll()->getDocs();
            foreach($comptes as $compte) {
                $choices[$compte["identifiant"]] = $compte["nom"];
            }
            
        }

        return $choices;
    }

    /**
     * 
     * @return _Tiers;
     */
    public function process() {
        if ($this->isValid()) {
            return $this->getValue('compte');
        } else {
            throw new sfException("must be valid");
        }
    }

}

