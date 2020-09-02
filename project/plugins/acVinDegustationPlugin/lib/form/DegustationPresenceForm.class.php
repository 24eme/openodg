<?php

class DegustationPresenceForm extends acCouchdbObjectForm
{
    public function configure() {
        $this->widgetSchema->setNameFormat('degustation_presence[%s]');
    }

    protected function doUpdateObject($values) {
        parent::doUpdateObject($values);
        
        $this->getObject()->validate();
    }
}
