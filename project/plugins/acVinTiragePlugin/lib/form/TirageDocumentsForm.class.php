<?php

class TirageDocumentsForm extends acCouchdbObjectForm
{

    public function configure() 
    {
        foreach ($this->getObject() as $type => $document) {
            $this->embedForm($type, new TirageDocumentForm($document));
        }
        $this->widgetSchema->setNameFormat('documents[%s]');
    }

    protected function doUpdateObject($values) 
    {
        foreach ($this->getEmbeddedForms() as $key => $embedForm) {
            $embedForm->doUpdateObject($values[$key]);     
        }
        
    }

}