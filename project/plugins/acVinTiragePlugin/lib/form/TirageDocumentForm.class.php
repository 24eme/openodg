<?php

class TirageDocumentForm extends acCouchdbObjectForm
{
    public function configure()
    {
        $this->setWidget('statut', new sfWidgetFormInputCheckbox());
        $this->getWidget('statut')->setLabel("Document reçu");
        $this->setValidator('statut',  new sfValidatorBoolean(array('required' => false)));
        $this->widgetSchema->setNameFormat('[%s]');
    }
    
    public function doUpdateObject($values) {
        parent::doUpdateObject($values);
        if(isset($values['statut']) && $values['statut']) {
            $this->getObject()->set('statut', TirageDocuments::STATUT_RECU);
        } else {
            $this->getObject()->set('statut', TirageDocuments::STATUT_EN_ATTENTE);
        }
    }
    


    protected function updateDefaultsFromObject() {
        parent::updateDefaultsFromObject();
        $defaults = $this->getDefaults();
        if ($this->getObject()->statut == TirageDocuments::STATUT_RECU) {
            $defaults['statut'] = true;
        } else {
            $defaults['statut'] = false;
        }
        $this->setDefaults($defaults);
    }

}