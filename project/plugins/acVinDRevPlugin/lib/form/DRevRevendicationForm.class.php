<?php
class DRevRevendicationForm extends acCouchdbObjectForm
{
	public function configure()
    {
				$produits = array();
				foreach ($this->getObject()->declaration->getProduits()	 as $key => $produit) {
					if(!$produit->getConfig()->isRevendicationParLots()){
						$produits[$key] =	$produit;
					}
				}

        $this->embedForm('produits', new DRevRevendicationProduitsForm($produits, array(), $this->getOptions()));
        //$this->validatorSchema->setPostValidator(new DRevRevendicationProduitValidator());
        $this->widgetSchema->setNameFormat('drev_produits[%s]');
    }

    protected function doUpdateObject($values)
    {
        parent::doUpdateObject($values);
        foreach ($this->getEmbeddedForms() as $key => $embedForm) {
        	$embedForm->doUpdateObject($values[$key]);
        }
    }

}
