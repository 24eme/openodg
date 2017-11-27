<?php

class CompteFormationForm extends acCouchdbObjectForm {

    public function configure() {

        $this->setWidgets(array(
            'produit_hash' => new sfWidgetFormChoice(array('choices' => $this->getAllProduits())),
            'annee' => new sfWidgetFormChoice(array('choices' => $this->getAnnees())),
            'heures' => new sfWidgetFormInputFloat(),
        ));
        $this->widgetSchema->setLabels(array(
            'produit_hash' => 'Produit',
            'annee' => 'Année',
            'heures' => 'Heure',
        ));
        $this->setValidators(array(
            'produit_hash' => new sfValidatorChoice(array('required' => false, 'choices' => array_keys($this->getAllProduits()))),
            'annee' => new sfValidatorChoice(array('required' => false, 'choices' => array_keys($this->getAnnees()))),
            'heures' => new sfValidatorNumber(array('required' => false)),
        ));

        $this->validatorSchema->setPostValidator(new ValidatorCompteFormation());

        $this->widgetSchema->setNameFormat('compte_formation[%s]');
    }

    protected function getAnnees() {
        $fromAnnee = date('Y');
        $toAnnee = "2012";
        $annees = array("" => "");

        for($i = $fromAnnee; $i >= $toAnnee; $i--) {

            $annees[$i] = $i;
        }

        return $annees;
    }

    protected function getAllProduits() {
        $produits = array("" => "");

        foreach (ConfigurationClient::getConfiguration()->declaration->getProduitsFilter(_ConfigurationDeclaration::TYPE_DECLARATION_DEGUSTATION, 'ConfigurationAppellation') as $hash => $produit) {
            $produits[$produit->getHash()] = $produit->getLibelleComplet();
        }

        return $produits;
    }

}
