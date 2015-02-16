<?php

class ParcellaireAcheteursForm extends acCouchdbForm {

    public function __construct(acCouchdbDocument $doc, $defaults = array(), $options = array(), $CSRFSecret = null) {
        $defaults = $this->getDefaultsByDoc($doc);
        parent::__construct($doc, $defaults, $options, $CSRFSecret);
    }

    public function configure() {
        
        foreach($this->getDocument()->declaration->getProduits() as $cepage) {
            $this->setWidget($cepage->getHash(), new sfWidgetFormChoice(array('choices' => $this->getAcheteurs(), 'multiple' => true, 'expanded' => true)));
            $this->setValidator($cepage->getHash(), new sfValidatorChoice(array('choices' => array_keys($this->getAcheteurs()), 'multiple' => true, 'required' => false)));   
            $this->getWidget($cepage->getHash())->setLabel($cepage->getLibelleComplet());             
        }

        $this->widgetSchema->setNameFormat('parcellaire_acheteurs[%s]');
    }

    public function getDefaultsByDoc($doc) {
        $defaults = array();

        foreach($doc->getProduits() as $hash => $produit) {
            foreach($produit->acheteurs as $type => $acheteurs) {
                foreach($acheteurs as $acheteur) {
                    if(!isset($defaults[$hash])) {
                        $defaults[$hash] = array();
                    }
                    $defaults[$hash] = array_merge($defaults[$hash], array(str_replace($hash, "", $acheteur->getHash())));
                }
            }
        }

        return $defaults;
    }

    public function getAcheteurs() {
        $acheteurs = array();

        foreach($this->getDocument()->acheteurs as $achs) {
            foreach($achs as $acheteur) {
                $acheteurs[$acheteur->getHash()] = sprintf("%s", $acheteur->nom);
            }
        }

        return $acheteurs;
    }

    public function update() {
        foreach($this->getDocument()->getProduits() as $produit) {
                $produit->remove('acheteurs');
                $produit->add('acheteurs');
        }
        foreach($this->values as $hash_produit => $hash_acheteurs) {
            if(!is_array($hash_acheteurs)) {
                continue;
            }
            
            foreach($hash_acheteurs as $hash_acheteur) {
                $produit = $this->getDocument()->get($hash_produit);
                $acheteur = $this->getDocument()->get($hash_acheteur);
                $produit->addAcheteurFromNode($acheteur);
            }
        }
    }

    public function save() {
        $this->getDocument()->save();
    }
}
