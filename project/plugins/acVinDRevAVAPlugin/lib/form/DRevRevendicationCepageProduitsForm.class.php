<?php
class DRevRevendicationCepageProduitsForm extends sfForm 
{
    protected $produits;
    
    public function __construct($produits, $defaults = array(), $options = array(), $CSRFSecret = null)
    {
        $this->produits = $produits;
        parent::__construct($defaults, $options, $CSRFSecret);
    }
    
    public function configure()
    {
        foreach ($this->produits as $hash => $produit) {
            $this->embedForm($hash, new DRevRevendicationCepageProduitForm($produit));
        }
    }

    public function doUpdateObject($values) 
    {
        foreach ($this->getEmbeddedForms() as $key => $embedForm) {
            $embedForm->doUpdateObject($values[$key]);
        }
    }
}