<?php
class DRevMarcValidationForm extends acCouchdbForm 
{    
	public function configure()
    {            
        $this->widgetSchema->setNameFormat('drevmarc_validation[%s]');
    }
    
}