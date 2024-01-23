<?php

class DRevDocumentsForm extends acCouchdbObjectForm
{

    public function configure() 
    {
    	foreach ($this->getObject() as $type => $document) {
            if(in_array($type, [DRevDocuments::DOC_DR, DRevDocuments::DOC_SV]) && $this->getObject()->getDocument()->hasDROrSV()) {
                continue;
            }
            $this->embedForm($type, new DRevDocumentForm($document));
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