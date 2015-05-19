<?php

class FactureEditionLigneDetailForm extends acCouchdbObjectForm {

    public function configure()
    {
        $this->setWidget("quantite", new sfWidgetFormInput());
        $this->setValidator("quantite", new sfValidatorNumber(array("required" => false)));

        $this->setWidget("libelle", new sfWidgetFormInput());
        $this->setValidator("libelle", new sfValidatorString(array("required" => true)));
      
        $this->setWidget("prix_unitaire", new sfWidgetFormInputFloat());
        $this->setValidator("pix_unitaire", new sfValidatorNumber(array('required' => false)));

        $this->setWidget("montant_ht", new sfWidgetFormInputFloat());
        $this->setValidator("montant_ht", new sfValidatorNumber(array('required' => false)));

        $this->setWidget("taux_tva", new sfWidgetFormInputFloat());
        $this->setValidator("taux_tva", new sfValidatorNumber(array('required' => false)));

        $this->setWidget("montant_tva", new sfWidgetFormInputFloat());
        $this->setValidator("montant_tva", new sfValidatorNumber(array('required' => false)));

        $this->widgetSchema->setNameFormat('facture_edition_ligne[%s]');
    }     

}
