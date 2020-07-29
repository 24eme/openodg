<?php

class TourneeSaisieDegustateurForm extends acCouchdbForm {

    public function configure() {
        $this->setWidget('compte', new WidgetCompte(array('type_compte' => CompteClient::TYPE_COMPTE_DEGUSTATEUR)));
        $this->setValidator('compte', new sfValidatorString(array("required" => true)));
    }

}
