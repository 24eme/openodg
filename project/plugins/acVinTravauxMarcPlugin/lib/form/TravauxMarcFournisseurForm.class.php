<?php

class TravauxMarcFournisseurForm extends acCouchdbObjectForm {

    public function configure() {
        $this->setWidget('etablissement_id', new WidgetCompte(array('type_compte' => CompteClient::TYPE_COMPTE_ETABLISSEMENT)));
        $this->setValidator('etablissement_id', new sfValidatorString(array("required" => false)));

        $this->setWidget('date_livraison', new sfWidgetFormInput());
        $this->setValidator('date_livraison', new sfValidatorDate(array('date_output' => 'Y-m-d', 'date_format' => '~(?P<day>\d{2})/(?P<month>\d{2})/(?P<year>\d{4})~', 'required' => false)));

        $this->setWidget('quantite', new sfWidgetFormInputFloat());
        $this->setValidator('quantite', new sfValidatorNumber(array("required" => false)));
        $this->getWidget('quantite')->setLabel("Quantité de marc mise en oeuvre :");
    }

    public function updateDefaultsFromObject() {
        parent::updateDefaultsFromObject();
        $this->setDefault('etablissement_id', str_replace('ETABLISSEMENT-', 'COMPTE-E', $this->getDefault('etablissement_id')));
        $this->setDefault('date_livraison', $this->getObject()->getDateLivraisonFr());
    }

}
