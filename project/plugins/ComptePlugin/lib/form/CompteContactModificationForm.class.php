<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of CompteContactModificationForm
 *
 * @author mathurin
 */
class CompteContactModificationForm extends CompteModificationForm {

    public function configure() {
        parent::configure();

        $this->setWidget("civilite", new sfWidgetFormChoice(array('choices' => $this->getCivilites())));
        $this->setWidget("prenom", new sfWidgetFormInput(array("label" => "Prénom")));
        $this->setWidget("nom", new sfWidgetFormInput(array("label" => "Nom")));

        $this->setWidget("raison_sociale", new sfWidgetFormInput(array("label" => "Société")));

        $this->setValidator('civilite', new sfValidatorChoice(array('required' => true, 'choices' => array_keys($this->civilites)), array('required' => "Aucune civilité choisie.")));
        $this->setValidator('prenom', new sfValidatorString(array("required" => false)));
        $this->setValidator('nom', new sfValidatorString(array("required" => false)));

        $this->setValidator('raison_sociale', new sfValidatorString(array("required" => false)));
    }

}
