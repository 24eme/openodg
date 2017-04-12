<?php

class TourneeSaisieCreationForm extends acCouchdbObjectForm
{
    public function configure() {
        $produits = $this->getProduits();

        $this->setWidget('date', new sfWidgetFormInput(array(), array()));
        $this->setValidator('date', new sfValidatorDate(array('date_output' => 'Y-m-d', 'date_format' => '~(?P<day>\d{2})/(?P<month>\d{2})/(?P<year>\d{4})~', 'required' => true)));

        $this->setWidget('produit', new bsWidgetFormChoice(array('choices' => $produits)));
        $this->setValidator('produit', new sfValidatorChoice(array('required' => true, 'choices' => array_keys($produits)), array('required' => "Le lieu-dit est requis")));

        $this->setWidget('millesime', new sfWidgetFormInput(array(), array()));
        $this->setValidator('millesime', new sfValidatorInteger(array('required' => true)));

        $this->setWidget('organisme', new sfWidgetFormInput(array(), array()));
        $this->setValidator('organisme', new sfValidatorString(array('required' => true)));

        $this->widgetSchema->setNameFormat('tournee_saisie_creation[%s]');
    }

    public function getProduits() {
        $lieux = ConfigurationClient::getConfiguration()->declaration
                                                        ->certification
                                                        ->genre
                                                        ->get("appellation_".$this->getObject()->appellation)
                                                        ->getLieux();
        $produits = array("" => "");

        foreach ($lieux as $key => $lieu) {
            $produits[$lieu->getHash()] = $lieu->getLibelle();
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

    public function doUpdateObject($values)
    {
        parent::doUpdateObject($values);
        $this->getObject()->appellation_complement = strtoupper(preg_replace("/[_-]+/", "", KeyInflector::slugify($this->getObject()->getProduitConfig()->getLibelle())));
        $this->getObject()->constructId();
    }

}
