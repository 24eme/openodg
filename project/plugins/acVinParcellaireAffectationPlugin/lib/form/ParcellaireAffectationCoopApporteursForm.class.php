<?php

class ParcellaireAffectationCoopApporteursForm extends acCouchdbForm {

    public function __construct($doc) {
        $defaults = array();
        foreach($doc->getApporteursChoisis() as $id => $apporteur) {
            $defaults[$id] = 1;
        }
      parent::__construct($doc, $defaults);
    }

    public function configure() {
        foreach ($this->getDocument()->apporteurs as $id => $apporteur) {
            $this->setWidget($id, new WidgetFormInputCheckbox());
            $this->setValidator($id, new ValidatorBoolean());
        }
        $this->widgetSchema->setNameFormat('apporteurs[%s]');
    }

    public function save() {
        $values = $this->getValues();
        foreach ($this->getDocument()->getApporteurs() as $id => $apporteur) {
            $apporteur->apporteur = boolval($values[$id]);
        }
        $this->getDocument()->updateApporteurs();
        $this->getDocument()->save();
    }

}
