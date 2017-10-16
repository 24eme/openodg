<?php
class DRevSuperficieForm extends acCouchdbObjectForm
{
	public function configure()
    {
		$this->embedForm('produits', new DRevSuperficieProduitsForm($this->getObject()->declaration->getProduits()));
        $this->validatorSchema->setPostValidator(new DRevSuperficieProduitValidator());
        $this->widgetSchema->setNameFormat('drev_superficie[%s]');
    }

    protected function doUpdateObject($values)
    {
        parent::doUpdateObject($values);
        foreach ($this->getEmbeddedForms() as $key => $embedForm) {
        	$embedForm->doUpdateObject($values[$key]);
        }
    }
}
