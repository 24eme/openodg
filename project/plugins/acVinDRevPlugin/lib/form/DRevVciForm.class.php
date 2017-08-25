<?php
class DRevVciForm extends acCouchdbObjectForm
{
	public function configure()
    {
        $this->embedForm('produits', new DRevVciProduitsForm($this->getObject()->declaration->getProduits()));
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
