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
            /*if(!array_key_exists(EtablissementClient::FAMILLE_VINIFICATEUR, $etablissement["familles"]) && !array_key_exists(EtablissementClient::FAMILLE_DISTILLATEUR, $etablissement["familles"])) {
                
                continue;
            }*/
            $choices[$etablissement["identifiant"]] = sprintf("%s - %s %s - %s (%s)", 
                $etablissement["nom"], 
                $etablissement["code_postal"], 
                $etablissement["commune"], 
                $etablissement["identifiant"],
                implode(", ", array_keys($etablissement["familles"])));
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

