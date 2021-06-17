<?php

class ParcellaireAffectationAcheteursForm extends acCouchdbForm {

    public function __construct(acCouchdbDocument $doc, $defaults = array(), $options = array(), $CSRFSecret = null) {
        parent::__construct($doc, $defaults, $options, $CSRFSecret);
        $this->updateDefaults($doc);
    }

    public static function buildLibelle($cepage, $hash) {
        $lieu_libelle = self::buildLieuLibelle($cepage, $hash);

        if ($cepage->getCouleur()->getLieu()->getAppellation()->getKey() == 'appellation_'.ParcellaireAffectationClient::APPELLATION_ALSACEBLANC) {
            $lieu_libelle = "VT/SGN";
        }

        return sprintf("%s - %s - %s",
            ParcellaireAffectationClient::getAppellationLibelle($cepage->getCouleur()->getLieu()->getAppellation()->getKey()),
            $lieu_libelle,
            $cepage->libelle
        );
    }

    public static function buildLieuLibelle($cepage, $hash) {
        $lieux_editable = $cepage->getDocument()->declaration->getLieuxEditable();

        $lieu_libelle = $cepage->getCouleur()->getLieu()->getLibelle();
        if($cepage->getConfig()->hasLieuEditable()) {
            $lieu_libelle = $lieux_editable[preg_replace("|^.*/lieu([^/]*)/.+$|", '\1', $hash)];
        }

        return $lieu_libelle;
    }

    public function configure() {

        $produits = $this->getDocument()->declaration->getProduitsWithLieuEditable();
        ksort($produits);

        foreach($produits as $hash => $cepage) {
            $lieu_affecte = null;
            if($cepage->getConfig()->hasLieuEditable()) {
                $lieu_affecte = self::buildLieuLibelle($cepage, $hash);
            }
            if(!$cepage->isAffectee($lieu_affecte)) {
            	continue;
            }
            $this->setWidget($hash, new sfWidgetFormChoice(array('choices' => $this->getAcheteurs(), 'multiple' => true, 'expanded' => true)));
            $this->setValidator($hash, new sfValidatorChoice(array('choices' => array_keys($this->getAcheteurs()), 'multiple' => true, 'required' => false)));
            $this->getWidget($hash)->setLabel(self::buildLibelle($cepage, $hash));
        }

        if($this->hasProduits() > 0) {
            $this->validatorSchema->setPostValidator(new ParcellaireAffectationAcheteursValidator(null, array("acheteurs" => $this->getAcheteurs())));
        }

        $this->widgetSchema->setNameFormat('parcellaire_acheteurs[%s]');
    }

    public function hasProduits() {

        return count($this->getDocument()->declaration->getProduitsWithLieuEditable()) > 0;
    }

    public function updateDefaults() {
        $defaults = $this->getDefaults();

        $produits = $this->getDocument()->declaration->getProduitsWithLieuEditable();

        if(count($this->getAcheteurs()) == 1) {
            $key_acheteur = key($this->getAcheteurs());
            foreach($produits as $hash => $produit) {
                $defaults[$hash] = array($key_acheteur);
            }
        }

        foreach($produits as $hash => $produit) {
            $lieu_key = $produit->getLieuKeyFromHash($hash);
            foreach($produit->getAcheteursNode($lieu_key) as $type => $acheteurs) {
                foreach($acheteurs as $acheteur) {
                    if(!isset($defaults[$hash])) {
                        $defaults[$hash] = array();
                    }
                    $key = sprintf("/acheteurs/%s/%s", $acheteur->getParent()->getKey(), $acheteur->getKey());
                    if(in_array($key, $defaults[$hash])) {
                        continue;
                    }
                    $defaults[$hash] = array_merge($defaults[$hash], array($key));
                }
            }
        }

        $this->setDefaults($defaults);
    }

    public function getAcheteurs() {

        return $this->getDocument()->getAcheteursByHash();
    }

    public function update() {
        foreach($this->getDocument()->getProduits() as $produit) {
                $produit->remove('acheteurs');
                $produit->add('acheteurs');
        }

        $produits = $this->getDocument()->declaration->getProduitsWithLieuEditable();

        foreach($this->values as $hash_produit_value => $hash_acheteurs) {
            if(!is_array($hash_acheteurs)) {
                continue;
            }

            foreach($hash_acheteurs as $hash_acheteur) {
                $hash_produit = $produits[$hash_produit_value]->getHash();
                $produit = $this->getDocument()->get($hash_produit);
                $acheteur = $this->getDocument()->get($hash_acheteur);
                $lieu = null;
                if($produit->getConfig()->hasLieuEditable()) {
                    $lieu = preg_replace("|^.*/lieu([^/]*)/.+$|", '\1', $hash_produit_value);
                }
                $produit->addAcheteurFromNode($acheteur, $lieu);
            }
        }
    }

    public function save() {
        $this->getDocument()->save();
    }
}
