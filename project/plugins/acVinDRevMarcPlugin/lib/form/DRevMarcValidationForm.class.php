<?php
class DRevMarcValidationForm extends acCouchdbObjectForm 
{    
	public function configure()
    {            
        $this->widgetSchema->setNameFormat('drevmarc_validation[%s]');
    }
    
    protected function doUpdateObject($values) 
    {
        $this->getObject()->validate();
        parent::doUpdateObject($values);
    }
}