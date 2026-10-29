<?php

class EtablissementForm extends acCouchdbObjectForm
{
	protected $updatedValues;

    public function __construct(\acCouchdbJson $object, $options = array(), $CSRFSecret = null) {
        if (!$object->getDocument() instanceof Etablissement) {
            $object = $object->getDocument()->getEtablissementObject();
        }

        parent::__construct($object, $options, $CSRFSecret);
        $this->updatedValues = array();
    }

     public function configure() {
       $this->setWidgets(array(
            "telephone_bureau" => new sfWidgetFormInput(array("label" => "Tél. Bureau")),
            "telephone_mobile" => new sfWidgetFormInput(array("label" => "Tél. Mobile")),
            "telephone_prive" => new sfWidgetFormInput(array("label" => "Tél. Privé")),
            "fax" => new sfWidgetFormInput(array("label" => "Fax")),
            "email" => new sfWidgetFormInput(array("label" => "Email")),
        ));

        $this->setValidators(array(
            'telephone_bureau' => new sfValidatorString(array("required" => false)),
            'telephone_mobile' => new sfValidatorString(array("required" => false)),
            'telephone_prive' => new sfValidatorString(array("required" => false)),
            'fax' => new sfValidatorString(array("required" => false)),
       	    'email' => new sfValidatorEmailStrict(array("required" => true)),
        ));

        if(!$this->getOption("use_email")) {
            $this->getValidator('email')->setOption('required', false);
        }

        if($this->getObject()->exist('siren') && $this->getObject()->identifiant == $this->getObject()->siren) {
            unset($this['siret']);
        }

        $this->widgetSchema->setNameFormat('etablissement[%s]');
    }

    public function save($con = null) {

        parent::save($con);
        $this->getObject()->updateCompte();
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
