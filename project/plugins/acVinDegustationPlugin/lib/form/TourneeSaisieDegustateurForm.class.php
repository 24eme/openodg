<?php

class TourneeSaisieDegustateurForm extends acCouchdbForm {

    public function configure() {
        $this->setWidget('compte', new bsWidgetFormInput());
        $this->setValidator('compte', new sfValidatorString(array("required" => true)));
    }

}
