<?php

class TourneeSaisieCreationForm extends acCouchdbObjectForm
{
    public function configure() {
        $produits = $this->getProduits();

        $this->setWidget('date', new sfWidgetFormInput(array(), array()));
        $this->setValidator('date', new sfValidatorDate(array('date_output' => 'Y-m-d', 'date_format' => '~(?P<day>\d{2})/(?P<month>\d{2})/(?P<year>\d{4})~', 'required' => true)));

        $this->setWidget('produit', new bsWidgetFormChoice(array('choices' => $produits)));
        $this->setValidator('produit', new sfValidatorChoice(array('required' => true, 'choices' => array_keys($produits)), array('required' => "Le produit est requis")));

        $this->setWidget('millesime', new sfWidgetFormInput(array(), array()));
        $this->setValidator('millesime', new sfValidatorInteger(array('required' => true)));

        $this->setWidget('organisme', new sfWidgetFormInput(array(), array()));
        $this->setValidator('organisme', new sfValidatorString(array('required' => true)));

        $this->widgetSchema->setNameFormat('tournee_saisie_creation[%s]');
    }

    public function getProduits() {
        $produitsConfig = ConfigurationClient::getConfiguration()->declaration->getProduitsFilter(_ConfigurationDeclaration::TYPE_DECLARATION_DEGUSTATION, "ConfigurationLieu");
        $produits = array("" => "");

        foreach ($produitsConfig as $hash => $produit) {
            $produits[$hash] = $produit->getLibelleComplet();
        }

        return $produits;
    }

    protected function updateDefaultsFromObject() {
        parent::updateDefaultsFromObject();

        if($this->getObject()->date) {
            $date = new DateTime($this->getObject()->date);
            $this->setDefault('date', $date->format('d/m/Y'));
        }

        $this->setDefault("millesime", (date("Y") - 1));
        $this->setDefault("organisme", "Gestion locale");
    }

}
