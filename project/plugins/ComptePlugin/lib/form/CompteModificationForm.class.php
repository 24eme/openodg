<?php

class CompteModificationForm extends acCouchdbObjectForm {

    const CIVILITE_MADEMOISELLE = "MLLE";
    const CIVILITE_MADAME = "MME";
    const CIVILITE_MONSIEUR = "M";

    private $civilites;
    private $attributsForCompte;
    private $tagsManuelsForCompte;

    public function __construct(\acCouchdbJson $object, $options = array(), $CSRFSecret = null) {
        parent::__construct($object, $options, $CSRFSecret);
        $this->initDefaultAttributs();
        $this->initDefaultTagsManuels();
    }

    public function configure() {
        $this->setWidget("adresse", new sfWidgetFormInput(array("label" => "Adresse")));
        $this->setWidget("adresse_complement_destinataire", new sfWidgetFormInput(array("label" => "Adresse")));
        $this->setWidget("adresse_complement_lieu", new sfWidgetFormInput(array("label" => "Adresse")));
        $this->setWidget("code_postal", new sfWidgetFormInput(array("label" => "Code Postal")));
        $this->setWidget("commune", new sfWidgetFormInput(array("label" => "Commune")));
        $this->setWidget("telephone_bureau", new sfWidgetFormInput(array("label" => "Tél. Bureau")));
        $this->setWidget("telephone_mobile", new sfWidgetFormInput(array("label" => "Tél. Mobile")));
        $this->setWidget("telephone_prive", new sfWidgetFormInput(array("label" => "Tél. Privé")));
        $this->setWidget("fax", new sfWidgetFormInput(array("label" => "Fax")));
        $this->setWidget("email", new sfWidgetFormInput(array("label" => "Email")));
        $this->setWidget("attributs", new sfWidgetFormChoice(array('multiple' => true, 'choices' => $this->getAttributsForCompte())));
        $this->setWidget("manuels", new sfWidgetFormInput());
        $this->setWidget("commentaires", new sfWidgetFormTextarea());
        
        
        $this->setValidator('adresse', new sfValidatorString(array("required" => false)));
        $this->setValidator('adresse_complement_destinataire', new sfValidatorString(array("required" => false)));
        $this->setValidator('adresse_complement_lieu', new sfValidatorString(array("required" => false)));
        $this->setValidator('commune', new sfValidatorString(array("required" => true)));
        $this->setValidator('code_postal', new sfValidatorString(array("required" => true)));
        $this->setValidator('telephone_bureau', new ValidatorPhone(array("required" => false)));
        $this->setValidator('telephone_mobile', new ValidatorPhone(array("required" => false)));
        $this->setValidator('telephone_prive', new ValidatorPhone(array("required" => false)));
        $this->setValidator('fax', new ValidatorPhone(array("required" => false)));
        $this->setValidator('email', new sfValidatorEmailStrict(array("required" => false)));
        $this->setValidator('attributs', new sfValidatorChoice(array("required" => false, 'multiple' => true, 'choices' => array_keys($this->getAttributsForCompte()))));
        $this->setValidator('manuels', new sfValidatorString(array("required" => false)));
        $this->setValidator('commentaires', new sfValidatorString(array("required" => false)));

        if(!count($this->getAttributsForCompte())) {
            unset($this['attributs']);
        } 
        
        $this->widgetSchema->setNameFormat('compte_modification[%s]');

        $this->validatorSchema->setPostValidator(new ValidatorCompteModification());
    }

    protected function getCivilites() {
        if (!$this->civilites) {
            $this->civilites = array(self::CIVILITE_MONSIEUR => self::CIVILITE_MONSIEUR,
                self::CIVILITE_MADAME => self::CIVILITE_MADAME,
                self::CIVILITE_MADEMOISELLE => self::CIVILITE_MADEMOISELLE);
        }
        return $this->civilites;
    }

    private function getAttributsForCompte() {
        $compteClient = CompteClient::getInstance();
        if (!$this->attributsForCompte) {
            $this->attributsForCompte = $compteClient->getAttributsForType($this->getObject()->getTypeCompte());
        }
        return $this->attributsForCompte;
    }   
    
    public function save($con = null) {
        if (array_key_exists('attributs', $this->values)) {
            $attributs = ($this->values['attributs']) ? $this->values['attributs'] : array();
            $this->getObject()->updateInfosTagsAttributs($this->values['attributs']);
        }

        if (array_key_exists('manuels', $this->values)) {
            $tagsManuelsValues = $this->values['manuels'];
            $tagsManuelsValuesSplited = explode(",",$tagsManuelsValues);
            $tagsManuels = array();
            foreach ($tagsManuelsValuesSplited as $manuel) {
                $manuel_key = str_replace('-', '_',strtoupper(KeyInflector::slugify($manuel)));
                if(!$manuel_key && !$manuel) {
                    continue;
                }
                $tagsManuels[$manuel_key] = $manuel;
            }
            $this->getObject()->updateInfosTagsManuels($tagsManuels);
        }
        
        parent::save($con);
    }

    protected function doSave($con = null) {
        if (null === $con) {
            $con = $this->getConnection();
        }

        $this->updateObject();
        $this->object->getCouchdbDocument()->save(true, true);
    }

    private function initDefaultAttributs() {
        if(!isset($this['attributs'])) {
            return;
        }
        $default_attributs = array();
        foreach ($this->getObject()->getInfosAttributs() as $attribut_code => $attribut) {
            $default_attributs[] = $attribut_code;
        }
        $this->widgetSchema['attributs']->setDefault($default_attributs);
    }

    public function initDefaultTagsManuels() {

        $this->widgetSchema['manuels']->setDefault(implode(",", $this->getObject()->getInfosManuels()->toArray(true, false)));
    }

}
