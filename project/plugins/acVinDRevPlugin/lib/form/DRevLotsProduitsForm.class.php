<?php
class DRevLotsProduitsForm extends sfForm 
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
			$this->embedForm($produit->hash, new DRevLotsProduitForm($produit));
    	}
    }

    protected function doUpdateObject($values) 
    {
        foreach ($this->getEmbeddedForms() as $key => $embedForm) {
        	$embedForm->doUpdateObject($values[$key]);
        }
    }
}