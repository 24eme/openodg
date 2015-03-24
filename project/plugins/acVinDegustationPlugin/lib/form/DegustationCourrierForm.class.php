<?php

class DegustationCourrierForm extends acCouchdbObjectForm {

    public function configure() {

        foreach ($this->getObject()->getNotes() as $note) {
            $this->setWidget($note->prelevement->getHashForKey(), new sfWidgetFormChoice(array('choices' => $this->getTypesCourrier())));
            $this->setValidator($note->prelevement->getHashForKey(), new sfValidatorChoice(array('choices' => array_keys($this->getTypesCourrier()))));
        }

        $this->widgetSchema->setNameFormat('degustation_courrier[%s]');
    }

    public function getTypesCourrier() {
        return array_merge(array("" => ""), DegustationClient::$types_courrier_libelle);
    }

}
