<?php

class LoginForm extends BaseForm {
    
    /**
     * 
     */
    public function configure() {
        $this->setWidgets(array(
                'login'   => new sfWidgetFormInput(),
        ));

        $this->widgetSchema->setLabels(array(
                'login'  => 'Login : ',
        ));

        $this->setValidators(array(
                'login' => new sfValidatorString(array("required" => true)),
        ));
        
        $this->widgetSchema->setNameFormat('login[%s]');

        $this->validatorSchema['login']->setMessage('required', 'Champs obligatoire');
        $this->validatorSchema->setPostValidator(new ValidatorLogin());
       
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

