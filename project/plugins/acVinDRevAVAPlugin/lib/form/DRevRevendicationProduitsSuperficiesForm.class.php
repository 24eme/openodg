<?php

class DRevRevendicationProduitsSuperficiesForm extends sfForm
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
            $form = new DRevRevendicationProduitSuperficieForm($produit);
            $this->embedForm($hash, $form);
        }

    }

    public function doUpdateObject($values)
    {
        foreach ($this->getEmbeddedForms() as $key => $embedForm) {
            unset($values[$key]['_revision']);
        	$embedForm->doUpdateObject($values[$key]);
        }
    }


}
