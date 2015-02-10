<?php
class ParcellaireValidationForm extends acCouchdbForm 
{    
	public function configure()
    {            
        $this->widgetSchema->setNameFormat('parcellaire_validation[%s]');
    }
    
}