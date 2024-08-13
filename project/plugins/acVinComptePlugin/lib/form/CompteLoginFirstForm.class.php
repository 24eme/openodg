<?php
class CompteLoginFirstForm extends BaseForm {
    public function configure() {

        $this->setWidgets(array(
                'login'   => new bsWidgetFormInput(),
                'mdp'   => new bsWidgetFormInputPassword()
        ));

        $this->widgetSchema->setLabels(array(
                'login'  => 'Identifiant ODG : ',
                'mdp'  => 'Code de création (fourni par l\'ODG) : '
        ));

        $this->setValidators(array(
                'login' => new sfValidatorString(array('required' => true)),
                'mdp' => new sfValidatorString(array('required' => true, 'min_length' => 4)),
        ));

        $this->widgetSchema->setNameFormat('first_connection[%s]');

        $this->validatorSchema['login']->setMessage('required', 'Champ obligatoire');
        $this->validatorSchema['mdp']->setMessage('required', 'Champ obligatoire');


        $this->validatorSchema->setPostValidator(new ValidatorCompteLoginFirst());
    }
}
