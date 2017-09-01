<?php
class DRevVciForm extends acCouchdbObjectForm
{
	public function configure()
    {
		$produits = array();

		foreach($this->getObject()->declaration->getProduits() as $produit) {
			if(!$produit->vci_stock_initial) {
				continue;
			}
			$produits[$produit->getHash()] = $produit;
		}

		$this->embedForm('produits', new DRevVciProduitsForm($produits));
        $this->validatorSchema->setPostValidator(new DRevVciProduitValidator());
        $this->widgetSchema->setNameFormat('drev_vci[%s]');
    }

    protected function doUpdateObject($values)
    {
        parent::doUpdateObject($values);
        foreach ($this->getEmbeddedForms() as $key => $embedForm) {
        	$embedForm->doUpdateObject($values[$key]);
        }
    }
}
