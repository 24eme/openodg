<?php
class DRevLotsForm extends acCouchdbObjectForm 
{
	public function configure()
    {
        $this->embedForm('lots', new DRevLotsProduitsForm($this->getObject()->lots));
		$this->mergePostValidator(new DRevLotsValidator());
        $this->widgetSchema->setNameFormat('drev_lots[%s]');
    }
    
    public function doUpdateObject($values) 
    {
        parent::doUpdateObject($values);
        foreach ($this->getEmbeddedForms() as $key => $embedForm) {
        	$embedForm->doUpdateObject($values[$key]);
        }
    }
}