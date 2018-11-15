<?php
class DRevRevendicationSuperficiesForm extends acCouchdbObjectForm
{
	public function configure()
    {
        $this->embedForm('produits', new DRevRevendicationProduitsSuperficiesForm($this->getObject()->declaration->getProduits()));
//        $this->validatorSchema->setPostValidator(new DRevRevendicationSuperficieValidator());
        $this->widgetSchema->setNameFormat('drev_produits[%s]');
    }

    protected function doUpdateObject($values)
    {
        parent::doUpdateObject($values);
        foreach ($this->getEmbeddedForms() as $key => $embedForm) {
        	$embedForm->doUpdateObject($values[$key]);
        }

        $this->getObject()->getDocument()->updatePrelevementsFromRevendication();
    }
}
