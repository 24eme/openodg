<?php
class DRevRevendicationForm extends acCouchdbObjectForm
{
	public function configure()
    {
        if ($this->getOption('with_empty', false)) {
            $produits = $this->getObject()->declaration->getProduitsWithoutLots(null, true);
        } else {
            $produits = $this->getObject()->declaration->getProduitsWithoutLots();
        }

        $this->embedForm('produits', new DRevRevendicationProduitsForm($produits, array(), $this->getOptions()));
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
