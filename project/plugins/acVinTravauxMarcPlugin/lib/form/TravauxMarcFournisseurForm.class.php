<?php

class TravauxMarcFournisseurForm extends acCouchdbObjectForm {

    public function configure() {
        $this->setWidget('nom', new sfWidgetFormInput());
        $this->setValidator('nom', new sfValidatorString(array("required" => false)));

        $this->setWidget('date_livraison', new sfWidgetFormInput());
        $this->setValidator('date_livraison', new sfValidatorDate(array('date_output' => 'Y-m-d', 'date_format' => '~(?P<day>\d{2})/(?P<month>\d{2})/(?P<year>\d{4})~', 'required' => false)));
        $this->getValidator('date_livraison')->setMessage('bad_format', "Le format de la date n'est pas correct");

        $this->setWidget('quantite', new sfWidgetFormInputFloat());
        $this->setValidator('quantite', new sfValidatorNumber(array("required" => false)));

    }

    public function updateDefaultsFromObject() {
        parent::updateDefaultsFromObject();
        $this->setDefault('date_livraison', $this->getObject()->getDateLivraisonFr());
    }

}
