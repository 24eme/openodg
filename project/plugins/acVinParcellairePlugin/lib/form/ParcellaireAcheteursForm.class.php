<?php

class ParcellaireAcheteursForm extends acCouchdbForm {

    public function __construct(acCouchdbDocument $doc, $defaults = array(), $options = array(), $CSRFSecret = null) {
        $defaults = $this->getDefaultsByDoc($doc);
        parent::__construct($doc, $defaults, $options, $CSRFSecret);
    }

    public function configure() {
        
        foreach($this->getDocument()->declaration->getAppellations() as $appelation) {
            foreach($appelation->mention->getLieux() as $lieu) {
                $this->setWidget($lieu->getHash(), new sfWidgetFormChoice(array('choices' => $this->getAcheteurs(), 'multiple' => true, 'expanded' => true)));
                $this->setValidator($lieu->getHash(), new sfValidatorChoice(array('choices' => array_keys($this->getAcheteurs()), 'multiple' => true)));   
                $this->getWidget($lieu->getHash())->setLabel($lieu->getLibelleComplet());             
            }
        }

        $this->widgetSchema->setNameFormat('parcellaire_acheteurs[%s]');
    }

    public function getDefaultsByDoc($doc) {
        $defaults = array();

        foreach($doc->acheteurs as $acheteur) {
            foreach($acheteur->produits as $produit) {
                if(!array_key_exists($produit->hash_produit, $defaults)) {
                    $defaults[$produit->hash_produit] = array();
                }
                $defaults[$produit->hash_produit] = array_merge($defaults[$produit->hash_produit], array($acheteur->getKey()));
            }
        }

        return $defaults;
    }

    public function getAcheteurs() {
        $acheteurs = array("6823700100" => "BOXLER Justin", "6823700101" => "Arthur METZ");
        foreach($this->getDocument()->acheteurs as $acheteur) {
            $acheteurs[$acheteur->getKey()] = sprintf("%s (%s)", $acheteur->nom, $acheteur->cvi);
        }

        return $acheteurs;
    }

    public function update() {
        foreach($this->getDocument()->acheteurs as $acheteur) {
            $acheteur->remove('produits');
            $acheteur->add('produits');
        }

        foreach($this->values as $hash => $cvis) {
            if(!is_array($cvis)) {
                continue;
            }
            foreach($cvis as $cvi) {
                $acheteur = $this->getDocument()->acheteurs->add($cvi);
                $acheteur->cvi = $cvi;
                $produit = $acheteur->produits->add(str_replace("/", "-", $hash));
                $produit->hash_produit = $hash;
                $produit->libelle = $this->getDocument()->get($hash)->getLibelleComplet();
            }
        }
    }

    public function save() {
        $this->getDocument()->save();
    }
}
