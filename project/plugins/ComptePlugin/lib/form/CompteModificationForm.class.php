<?php

class CompteModificationForm extends acCouchdbObjectForm {

    const CIVILITE_MADEMOISELLE = "Mlle.";
    const CIVILITE_MADAME = "Mme.";
    const CIVILITE_MONSIEUR = "M.";

    private $civilites;
    private $attributsForCompte;

    public function __construct(\acCouchdbJson $object, $options = array(), $CSRFSecret = null) {
        parent::__construct($object, $options, $CSRFSecret);
        $this->initDefaultAttributs();
    }

    public function configure() {
                
        $this->setWidget("civilite", new sfWidgetFormChoice(array('choices' => $this->getCivilites())));
        $this->setWidget("prenom", new sfWidgetFormInput(array("label" => "Prénom")));
        $this->setWidget("nom", new sfWidgetFormInput(array("label" => "Nom")));
        
        $this->setWidget("raison_sociale", new sfWidgetFormInput(array("label" => "Société")));
        
        $this->setWidget("adresse", new sfWidgetFormInput(array("label" => "Adresse")));
        $this->setWidget("code_postal", new sfWidgetFormInput(array("label" => "Code Postal")));
        $this->setWidget("commune", new sfWidgetFormInput(array("label" => "Commune")));
        $this->setWidget("telephone_bureau", new sfWidgetFormInput(array("label" => "Tél. Bureau")));
        $this->setWidget("telephone_mobile", new sfWidgetFormInput(array("label" => "Tél. Mobile")));
        $this->setWidget("telephone_prive", new sfWidgetFormInput(array("label" => "Tél. Privé")));
        $this->setWidget("fax", new sfWidgetFormInput(array("label" => "Fax")));
        $this->setWidget("email", new sfWidgetFormInput(array("label" => "Email")));
        $this->setWidget("attributs", new sfWidgetFormChoice(array('multiple' => true, 'choices' => $this->getAttributsForCompte())));
        

        
        $this->setValidator('civilite', new sfValidatorChoice(array('required' => true, 'choices' => array_keys($this->civilites)), array('required' => "Aucune civilité choisie.")));
        $this->setValidator('prenom', new sfValidatorString(array("required" => false)));
        $this->setValidator('nom', new sfValidatorString(array("required" => false)));
        
         $this->setValidator('raison_sociale', new sfValidatorString(array("required" => false)));
                
        $this->setValidator('adresse', new sfValidatorString(array("required" => true)));
        $this->setValidator('commune', new sfValidatorString(array("required" => true)));
        $this->setValidator('code_postal', new sfValidatorString(array("required" => true)));
        $this->setValidator('telephone_bureau', new sfValidatorString(array("required" => false)));
        $this->setValidator('telephone_mobile', new sfValidatorString(array("required" => false)));
        $this->setValidator('telephone_prive', new sfValidatorString(array("required" => false)));
        $this->setValidator('fax', new sfValidatorString(array("required" => false)));
        $this->setValidator('email', new sfValidatorEmailStrict(array("required" => true)));
        $this->setValidator('attributs', new sfValidatorChoice(array('required' => false, 'multiple' => true, 'choices' => array_keys($this->getAttributsForCompte()))));

        $this->widgetSchema->setNameFormat('compte_modification[%s]');
    }

    private function getCivilites() {
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
        if ($attributs = $this->values['attributs']) {
            $this->getObject()->updateTagsAttributs($attributs);
        }
        parent::save($con);
    }

    private function initDefaultAttributs() {
        $default_attributs = array();
        foreach ($this->getObject()->getAttributs() as $attribut_code => $attribut) {
            $default_attributs[] = $attribut_code;
        }
        $this->widgetSchema['attributs']->setDefault($default_attributs);
    }

}
