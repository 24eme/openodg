<?php
class HabilitationDemandeDonneesProduitForm extends BaseForm
{
    protected $doc = null;
    protected $produits = array();

    public function getDocument() {

        return $this->doc;
    }

    public function __construct($doc, $defaults = array(), $options = array(), $CSRFSecret = null) {
        $this->doc = $doc;

        parent::__construct($defaults, $options, $CSRFSecret);
    }

    public function configure()
    {
        $produits = $this->getProduits();
        $activites = $this->getActivites();

        $this->setWidget('produit', new sfWidgetFormChoice(array('choices' => $produits)));
        $this->setWidget('activites', new sfWidgetFormChoice(array('expanded' => true, 'multiple' => true, 'choices' => $activites)));

        $this->widgetSchema->setLabel('produit', 'Produit: ');
        $this->widgetSchema->setLabel('activites', 'Activités: ');

        $this->setValidator('produit', new sfValidatorChoice(array('required' => true, 'choices' => array_keys($produits)),array('required' => "Aucun produit saisi.")));
        $this->setValidator('activites', new sfValidatorChoice(array('required' => true, 'multiple' => true, 'choices' => array_keys($activites))));
    }

    public function getProduits()
    {
        if (!$this->produits) {
            foreach ($this->getDocument()->getProduitsConfig() as $produit) {
                $this->produits[$produit->getHash()] = $produit->getLibelleComplet();
            }
        }
        return array_merge(array('' => ''), $this->produits);
    }

    public function getActivites(){

        return array_merge(HabilitationClient::$activites_libelles);
    }
}
