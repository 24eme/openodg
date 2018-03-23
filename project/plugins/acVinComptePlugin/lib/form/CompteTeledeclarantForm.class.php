<?php

class CompteTeledeclarantForm extends acCouchdbForm {
    protected $defaultEmail;
    protected $updatedValues;

    public function __construct($doc, $defaults = array(), $options = array(), $CSRFSecret = null) {
        $this->updatedValues = array();
        $societe = $doc->getSociete();

        $defaultEmail = null;
        if($societe->isTransaction()){
            $defaultEmail = $societe->getEtablissementPrincipal()->getEmailTeledeclaration();
        }else{
            $defaultEmail = $societe->getEmailTeledeclaration();
        }
        if(!$defaultEmail){
            $defaultEmail = $societe->email;
        }

        $defaults['email'] = $defaultEmail;
        $this->defaultEmail = $defaultEmail;
        
        if ($doc->telephone_mobile) {
        	$defaults['telephone_mobile'] = $doc->telephone_mobile;
        }
        
        if ($doc->telephone_bureau) {
        	$defaults['telephone_bureau'] = $doc->telephone_bureau;
        }

        parent::__construct($doc, $defaults, $options, $CSRFSecret);
    }

    public function getUpdatedValues()
    {
        return $this->updatedValues;
    }

    public function hasUpdatedValues()
    {
        return (count($this->updatedValues) > 0);
    }

    public function configure() {
        $this->setWidgets(array(
            'email' => new sfWidgetFormInputText(),
            'telephone_bureau' => new sfWidgetFormInputText(),
            'telephone_mobile' => new sfWidgetFormInputText(),
            'mdp1' => new sfWidgetFormInputPassword(),
            'mdp2' => new sfWidgetFormInputPassword()
        ));

        $this->widgetSchema->setLabels(array(
            'email' => 'Adresse e-mail* : ',
            'telephone_bureau' => 'Téléphone : ',
            'telephone_mobile' => 'Mobile : ',
            'mdp1' => 'Mot de passe* : ',
            'mdp2' => 'Vérification du mot de passe* : '
        ));

        $this->widgetSchema->setNameFormat('ac_vin_compte[%s]');

        $this->setValidator('email', new sfValidatorEmail(array('required' => true), array('required' => 'Champ obligatoire', 'invalid' => 'Adresse email invalide.')));
        $mdpValidator = new sfValidatorRegex(array('required' => false,
            'pattern' => "/^[^éèçùà€£µôûîêâöüïë§]+$/i",
            'min_length' => 8), array('required' => 'Le mot de passe est obligatoire',
            'invalid' => 'Le mot de passe doit être constitué de caractères non accentués',
            'min_length' => 'Le mot de passe doit être constitué de 8 caractères min.'));

        $this->setValidator('mdp1', $mdpValidator);
        $this->setValidator('mdp2', $mdpValidator);
        
        $this->setValidator('telephone_bureau', new sfValidatorString(array('required' => false)));
        $this->setValidator('telephone_mobile', new sfValidatorString(array('required' => false)));

        $this->validatorSchema->setPostValidator(new sfValidatorSchemaCompare('mdp1', sfValidatorSchemaCompare::EQUAL, 'mdp2', array(), array('invalid' => 'Les mots de passe doivent être identique.')));
    }

    public function save() {
        if (!$this->isValid())
        {
          throw $this->getErrorSchema();
        }

        if ($this->getValue('mdp1')) {
            $this->getDocument()->setMotDePasseSSHA($this->getValue('mdp1'));
        }

        if ($tel = $this->getValue('telephone_bureau')) {
        	$this->updatedValues['telephone_bureau'] = array($this->getDocument()->telephone_bureau, $tel);
            $this->getDocument()->telephone_bureau = $tel;
        }

        if ($mobile = $this->getValue('telephone_mobile')) {
        	$this->updatedValues['telephone_mobile'] = array($this->getDocument()->telephone_mobile, $mobile);
            $this->getDocument()->telephone_mobile = $mobile;
        }

        $this->getDocument()->add('teledeclaration_active', true);
        $this->getDocument()->save();

        $email = $this->getValue('email');
        
        if ($this->defaultEmail != $email) {
        	$this->updatedValues['email'] = array($this->defaultEmail, $email);
        }

        if(!$email) {
            return;
        }

        SocieteClient::getInstance()->clearSingleton();
        $societe = SocieteClient::getInstance()->find($this->getDocument()->id_societe);

        if ($societe->isTransaction()) {
            $allEtablissements = $societe->getEtablissementsObj();
            foreach ($allEtablissements as $etablissementObj) {
                $etb = $etablissementObj->etablissement;
                if ((!$etb->exist('email') || !$etb->email) && !$etb->isSameContactThanSociete()) {
                    $etb->email = $email;
                }
                $etb->add('teledeclaration_email', $email);
                $etb->save();
            }
        }

        if (!$societe->isTransaction()) {
            $societe->add('teledeclaration_email', $email);
            $societe->save();
        }

        if(!$societe->email) {
            $societe->email = $email;
            $societe->save();
        }
    }
}
