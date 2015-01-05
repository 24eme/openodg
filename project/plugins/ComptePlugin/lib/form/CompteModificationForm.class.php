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
        $this->setWidgets(array(
            "civilite" => new sfWidgetFormChoice(array('choices' => $this->getCivilites())),
            "nom" => new sfWidgetFormInput(array("label" => "Nom")),
            "prenom" => new sfWidgetFormInput(array("label" => "Prénom")),
            "adresse" => new sfWidgetFormInput(array("label" => "Adresse")),
            "code_postal" => new sfWidgetFormInput(array("label" => "Code Postal")),
            "ville" => new sfWidgetFormInput(array("label" => "Commune")),
            "telephone_bureau" => new sfWidgetFormInput(array("label" => "Tél. Bureau")),
            "telephone_mobile" => new sfWidgetFormInput(array("label" => "Tél. Mobile")),
            "telephone_prive" => new sfWidgetFormInput(array("label" => "Tél. Privé")),
            "fax" => new sfWidgetFormInput(array("label" => "Fax")),
            "email" => new sfWidgetFormInput(array("label" => "Email")),
            "siret" => new sfWidgetFormInput(array("label" => "N° SIRET/SIREN")),
            "attributs" => new sfWidgetFormChoice(array('multiple' => true, 'choices' => $this->getAttributsForCompte())),
        ));

        $this->setValidators(array(
            'civilite' => new sfValidatorChoice(array('required' => true, 'choices' => array_keys($this->civilites)), array('required' => "Aucune civilité choisie.")),
            'prenom' => new sfValidatorString(array("required" => false)),
            'nom' => new sfValidatorString(array("required" => false)),
            'adresse' => new sfValidatorString(array("required" => true)),
            'ville' => new sfValidatorString(array("required" => true)),
            'code_postal' => new sfValidatorString(array("required" => true)),
            'telephone_bureau' => new sfValidatorString(array("required" => false)),
            'telephone_mobile' => new sfValidatorString(array("required" => false)),
            'telephone_prive' => new sfValidatorString(array("required" => false)),
            'fax' => new sfValidatorString(array("required" => false)),
            'email' => new sfValidatorEmailStrict(array("required" => true)),
            'siret' => new sfValidatorRegex(array("required" => false, "pattern" => "/^[0-9]{14}$/"), array("invalid" => "Le siret doit être un nombre à 14 chiffres")),
            'attributs' => new sfValidatorChoice(array('required' => false, 'multiple' => true, 'choices' => array_keys($this->getAttributsForCompte()))),
        ));

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
