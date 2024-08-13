<?php

class ParcellaireAffectationCoopAjoutApporteursForm extends acCouchdbForm {

    public function configure() {
        $this->setWidget('cviApporteur', new bsWidgetFormInput());
        $this->getWidget('cviApporteur')->setLabel("Saisissez le CVI de l'apporteur à ajouter : ");
        $this->setValidator('cviApporteur', new sfValidatorString(array("required" => true)));
        $this->widgetSchema->setNameFormat('ajoutApporteurs[%s]');
    }
}
