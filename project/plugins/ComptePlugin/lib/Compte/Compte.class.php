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

    public function save($synchro_etablissement = true) {
        if ($this->isNew() && !$this->identifiant) {
            $this->identifiant = CompteClient::getInstance()->createIdentifiantForCompte($this);
        }

        if ($this->isTypeCompte(CompteClient::TYPE_COMPTE_ETABLISSEMENT) && $synchro_etablissement) {
            $etablissement = EtablissementClient::getInstance()->createOrFind($this->cvi);
            if ($this->isNew() && !$etablissement->isNew()) {
                throw new sfException("Pas possible de créer un etablissement avec cet Id");
            }
            $etablissement->synchroFromCompte($this);
            $etablissement->save();
            $this->setEtablissement($etablissement->_id);
        }

        $this->updateNomAAfficher();
        $this->updateInfosTagsAutomatiques();
        $this->updateTags();

        parent::save();
    }

    public function updateNomAAfficher() {
        $this->nom_a_afficher = "";

        if ($this->prenom) {
            $this->nom_a_afficher = trim(sprintf("%s %s %s", $this->civilite, $this->prenom, $this->nom));
        }

        if ($this->raison_sociale && $this->nom_a_afficher) {
            $this->nom_a_afficher .= " - ";
        }

        if ($this->raison_sociale) {
            $this->nom_a_afficher .= $this->raison_sociale;
        }
    }

    public function getInfosAttributs() {
        return $this->infos->get('attributs');
    }

    public function getInfosProduits() {
        return $this->infos->get('produits');
    }

    public function getInfosManuels() {
        return $this->infos->get('manuels');
    }

    public function getInfosAutomatiques() {
        return $this->infos->get('automatiques');
    }

    public function hasProduits() {
        return count($this->infos->get('produits'));
    }

    public function updateInfosTagsAttributs($attributs_array = array()) {
        foreach ($attributs_array as $attribut_code) {
            $this->updateInfosTags('attributs', $attribut_code, CompteClient::getInstance()->getAttributLibelle($attribut_code));
        }
    }

    public function updateInfosTagsManuels($infos_manuels = array()) {
        foreach ($infos_manuels as $info_manuel) {
            $info_manuel_key = str_replace(' ', '_', $info_manuel);
            $this->updateInfosTags('manuels', $info_manuel_key, $info_manuel);
        }
    }

    public function updateLocalTagsProduits($produits_hash_array = array()) {
        $allProduits = ConfigurationClient::getConfiguration()->getProduits();
        foreach ($produits_hash_array as $produits_hash) {
            $libelle_complet = $allProduits[str_replace('-', '/', $produits_hash)]->getLibelleComplet();
            $this->updateInfosTags('produits', $produits_hash, $libelle_complet);
        }
    }

    public function updateInfosTags($nodeType, $key, $value) {
        if (!$this->infos->exist($nodeType)) {
            $this->infos->add($nodeType, null);
        }
        $this->infos->$nodeType->add($key, $value);
    }

    public function isTypeCompte($type) {
        return $type == $this->getTypeCompte();
    }

    public function updateInfosTagsAutomatiques() {
        $this->updateInfosTags('automatiques', "TYPE_COMPTE", $this->getTypeCompte());
    }

    public function updateTags() {
        if ($this->exist('tags')) {
            $this->remove('tags');
        }
        $this->add('tags');
        foreach ($this->getInfosAttributs() as $key => $attribut) {
            $this->addTag('attributs', $this->formatTag($attribut));
        }
        foreach ($this->getInfosProduits() as $produit) {
            $this->addTag('produits', $this->formatTag($produit));
        }
        foreach ($this->getInfosManuels() as $key => $manuel) {
            $this->addTag('manuels', $this->formatTag($manuel));
        }
        foreach ($this->getInfosAutomatiques() as $automatique) {
            $this->addTag('automatiques', $this->formatTag($automatique));
        }
    }

    private function formatTag($tag) {
        return $tag;
    }

    public function addTag($nodeType, $value) {
        if (!$this->tags->exist($nodeType)) {
            $this->tags->add($nodeType, null);
        }
        $this->tags->$nodeType->add(null,$value);
    }

}
