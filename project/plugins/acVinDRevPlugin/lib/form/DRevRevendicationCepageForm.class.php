<?php
class DRevRevendicationCepageForm extends acCouchdbObjectForm 
{    
    public function configure()
    {
        $this->embedForm('produits', new DRevRevendicationCepageProduitsForm($this->getObject()->getProduitsCepage()));
        $this->widgetSchema->setNameFormat('drev_cepage_produits[%s]');
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