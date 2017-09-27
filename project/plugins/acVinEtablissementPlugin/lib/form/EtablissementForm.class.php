<?php

class EtablissementForm extends acCouchdbObjectForm
{
	protected $updatedValues;
	protected $coordonneesEtablissement = null;

    public function __construct(\acCouchdbJson $object, $options = array(), $CSRFSecret = null) {
        parent::__construct($object, $options, $CSRFSecret);
        $this->updatedValues = array();
    }

     public function configure() {
       $this->setWidgets(array(
            "siret" => new sfWidgetFormInput(array("label" => "N° SIRET")),
            //"raison_sociale" => new sfWidgetFormInput(array("label" => "Raison Sociale")),
            "adresse" => new sfWidgetFormInput(array("label" => "Adresse")),
            "commune" => new sfWidgetFormInput(array("label" => "Commune")),
            "code_postal" => new sfWidgetFormInput(array("label" => "Code Postal")),
            "telephone_bureau" => new sfWidgetFormInput(array("label" => "Tél. Bureau")),
            "telephone_mobile" => new sfWidgetFormInput(array("label" => "Tél. Mobile")),
            "telephone_prive" => new sfWidgetFormInput(array("label" => "Tél. Privé")),
            "fax" => new sfWidgetFormInput(array("label" => "Fax")),
            "email" => new sfWidgetFormInput(array("label" => "Email")),
        ));

        $this->setValidators(array(
            'siret' => new sfValidatorRegex(array("required" => false, "pattern" => "/^[0-9]{14}$/"), array("invalid" => "Le siret doit être un nombre à 14 chiffres")),
            //'raison_sociale' => new sfValidatorString(array("required" => false)),
            'adresse' => new sfValidatorString(array("required" => false)),
            'commune' => new sfValidatorString(array("required" => false)),
            'code_postal' => new sfValidatorString(array("required" => false)),
            'telephone_bureau' => new sfValidatorString(array("required" => false)),
            'telephone_mobile' => new sfValidatorString(array("required" => false)),
            'telephone_prive' => new sfValidatorString(array("required" => false)),
            'fax' => new sfValidatorString(array("required" => false)),
       	    'email' => new sfValidatorEmailStrict(array("required" => false)),
        ));

        if(!$this->getOption("use_email")) {
            $this->getValidator('email')->setOption('required', false);
        }

        if($this->getObject()->exist('siren') && $this->getObject()->identifiant == $this->getObject()->siren) {
            unset($this['siret']);
        }

        $this->widgetSchema->setNameFormat('etablissement[%s]');
    }

	private function getCoordonneesEtablissement() {
		if (!$this->coordonneesEtablissement) {
			$this->coordonneesEtablissement = $this->getObject();
		}
		return $this->coordonneesEtablissement;
	}

	public function updateDefaultsFromObject() {
        parent::updateDefaultsFromObject();
        $this->getCoordonneesEtablissement();
        $this->setDefault('adresse', $this->coordonneesEtablissement->adresse);
        $this->setDefault('code_postal', $this->coordonneesEtablissement->code_postal);
        $this->setDefault('commune', $this->coordonneesEtablissement->commune);
        $this->setDefault('email', $this->coordonneesEtablissement->email);
        $this->setDefault('siret', $this->coordonneesEtablissement->siret);
        $this->setDefault('telephone_prive', $this->coordonneesEtablissement->telephone_perso);
        $this->setDefault('telephone_bureau', $this->coordonneesEtablissement->telephone_bureau);
        $this->setDefault('telephone_mobile', $this->coordonneesEtablissement->telephone_mobile);
        $this->setDefault('fax', $this->coordonneesEtablissement->fax);

    }

    public function save($con = null) {

        parent::save($con);
        //$this->getObject()->updateCompte();
    }

    public function doUpdateObject($values) {
    	foreach ($this as $field => $widget) {
    		if (!$widget->isHidden()) {
    			if ($this->getObject()->exist($field) && $this->getObject()->get($field) != $values[$field]) {
    				$this->updatedValues[$field] = $this->getObject()->get($field);
    			}
    		}
    	}
        parent::doUpdateObject($values);
		$this->getObject()->setAdresse($values['adresse']);
		$this->getObject()->setCodePostal($values['code_postal']);
		$this->getObject()->setCommune($values['commune']);
		$this->getObject()->setEmail($values['email']);
		$this->getObject()->setSiret($values['siret']);
		$this->getObject()->setTelephonePerso($values['telephone_prive']);
		$this->getObject()->setTelephoneBureau($values['telephone_bureau']);
		$this->getObject()->setTelephoneMobile($values['telephone_mobile']);
		$this->getObject()->setFax($values['fax']);
    }

    public function getUpdatedValues()
    {
    	return $this->updatedValues;
    }

    public function hasUpdatedValues()
    {
    	return (count($this->updatedValues) > 0);
    }


}
