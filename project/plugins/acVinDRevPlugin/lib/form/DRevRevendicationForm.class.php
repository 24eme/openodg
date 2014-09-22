<?php
class DRevRevendicationForm extends acCouchdbObjectForm 
{    
	public function configure()
    {
        $this->embedForm('produits', new DRevRevendicationProduitsForm($this->getObject()->declaration->getProduits()));
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