<?php

/**
 * Model for Compte
 *
 */
class Compte extends BaseCompte {

    public function __construct($type_compte = null) {
        parent::__construct();
        $this->setTypeCompte($type_compte);
    }
    
    public function constructId() {
        $this->set('_id', 'COMPTE-' . $this->identifiant);
    }

    public function save() {
        if ($this->isNew() && !$this->identifiant) {
            $this->identifiant = CompteClient::getInstance()->createIdentifiantForCompte($this);
        }

        $this->updateNomAAfficher();

        parent::save();
    }

    public function updateNomAAfficher() {
        $this->nom_a_afficher = $this->civilite . ' ' . $this->prenom . ' ' . $this->nom;
    }

    public function getAttributs() {
        return $this->tags->get('attributs');
    }

    public function updateTagsAttributs($attributs_array = array()) {
        foreach ($attributs_array as $attribut_code) {
            $this->updateTags('attributs', $attribut_code, CompteClient::getInstance()->getAttributLibelle($attribut_code));
        }
    }

    public function updateTags($nodeType, $key, $value) {
        if (!$this->tags->exist($nodeType)) {
            $this->tags->add($nodeType, null);
        }
        $this->tags->$nodeType->add($key, $value);
    }
    
    public function isTypeCompte($type) {
        return $type == $this->getTypeCompte();
    }

}
