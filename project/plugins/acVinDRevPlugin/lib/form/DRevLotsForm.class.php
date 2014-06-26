<?php
class DRevLotsForm extends acCouchdbObjectForm 
{
	public function configure()
    {
        $this->embedForm('produits', new DRevLotsProduitsForm($this->getObject()->produits));
        $this->widgetSchema->setNameFormat('drev_lots_produits[%s]');
    }
    
    public function doUpdateObject($values) 
    {
        parent::doUpdateObject($values);
        foreach ($this->getEmbeddedForms() as $key => $embedForm) {
        	$embedForm->doUpdateObject($values[$key]);
        }
    }
}