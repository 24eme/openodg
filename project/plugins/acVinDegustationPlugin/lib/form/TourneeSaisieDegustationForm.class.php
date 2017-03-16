<?php

class TourneeSaisieDegustationForm extends acCouchdbForm {

    public function configure() {
        $produits = $this->getProduits();

        $this->setWidget('numero', new bsWidgetFormInput());
        $this->setValidator('numero', new sfValidatorString(array("required" => false)));

        $this->setWidget('etablissement', new bsWidgetFormInput());
        $this->setValidator('etablissement', new sfValidatorString(array("required" => false)));

        $this->setWidget('produit', new bsWidgetFormChoice(array('choices' => $produits)));
        $this->setValidator('produit', new sfValidatorChoice(array('required' => false, 'choices' => array_keys($produits)), array('required' => "Le produit est requis")));
    }

    public function getProduits() {
        $produitsConfig = ConfigurationClient::getConfiguration()->declaration->getProduitsFilter(_ConfigurationDeclaration::TYPE_DECLARATION_DREV_REVENDICATION_CEPAGE);
        $produits = array("" => "");
        foreach ($produitsConfig as $hash => $produit) {
            $produits[$hash] = $produit->getLibelleComplet();
        }

        return $produits;
    }

}
