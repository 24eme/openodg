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

        if($this->isTypeCompte(CompteClient::TYPE_COMPTE_ETABLISSEMENT)){
            $etablissement = EtablissementClient::getInstance()->createOrFind($this->cvi);
            if($this->isNew() && !$etablissement->isNew()){
                throw new sfException("Pas possible de créer un etablissement avec cet Id");
            }
            $etablissement->synchroFromCompte($this);
            $etablissement->save();
            $this->setEtablissement($etablissement->_id);
        }
        $this->updateNomAAfficher();

        parent::save();
    }

    public function updateNomAAfficher() {
        $this->nom_a_afficher = "";

        if($this->prenom) {
            $this->nom_a_afficher = trim(sprintf("%s %s %s", $this->civilite, $this->prenom, $this->nom)); 
        }

        if($this->raison_sociale && $this->nom_a_afficher) {
            $this->nom_a_afficher .= " - ";
        }

        if($this->raison_sociale) {
            $this->nom_a_afficher .= $this->raison_sociale;
        }
    }

    public function getAttributs() {
        return $this->tags->get('attributs');
    }

    public function updateTagsAttributs($attributs_array = array()) {
        foreach ($attributs_array as $attribut_code) {
            $this->updateTags('attributs', $attribut_code, CompteClient::getInstance()->getAttributLibelle($attribut_code));
        }
    }
    
    public function updateTagsProduits($produits_hash_array = array()) {
        $allProduits = ConfigurationClient::getConfiguration()->getProduits();
        foreach ($produits_hash_array as $produits_hash) {
            $libelle_complet = $allProduits[str_replace('-','/', $produits_hash)]->getLibelleComplet();
            $this->updateTags('produits', $produits_hash, $libelle_complet);
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
