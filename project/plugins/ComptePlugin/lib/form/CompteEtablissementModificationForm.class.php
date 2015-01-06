<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of CompteEtablissementModificationForm
 *
 * @author mathurin
 */
class CompteEtablissementModificationForm extends CompteModificationForm {
    
    public function configure() {
        parent::configure();
        
        $this->setWidget("raison_sociale", new sfWidgetFormInput(array("label" => "Raison sociale")));
      
         $this->setValidator('raison_sociale', new sfValidatorString(array("required" => true)));
                
        
        $this->setWidget("cvi", new sfWidgetFormInput(array("label" => "Cvi")));
        $this->setValidator('cvi', new sfValidatorRegex(array("required" => true, "pattern" => "/^[0-9]{10}$/"), array("invalid" => "Le cvi doit être un nombre à 10 chiffres")));
        
        $this->setWidget("code_insee", new sfWidgetFormInput(array("label" => "Code Insee")));
        $this->setValidator('code_insee', new sfValidatorRegex(array("required" => false, "pattern" => "/^[0-9]{5}$/"), array("invalid" => "Le code insee doit être un nombre à 5 chiffres")));
        
        $this->setWidget("siret", new sfWidgetFormInput(array("label" => "N° SIRET")));
        $this->setValidator('siret', new sfValidatorRegex(array("required" => false, "pattern" => "/^[0-9]{14}$/"), array("invalid" => "Le siret doit être un nombre à 14 chiffres")));
        
        $this->setWidget("siren", new sfWidgetFormInput(array("label" => "N° SIREN")));
        $this->setValidator('siren', new sfValidatorRegex(array("required" => false, "pattern" => "/^[0-9]{14}$/"), array("invalid" => "Le siren doit être un nombre à 14 chiffres")));
        
        $this->setWidget("siren", new sfWidgetFormInput(array("label" => "N° SIREN")));
        $this->setValidator('siren', new sfValidatorRegex(array("required" => false, "pattern" => "/^[0-9]{14}$/"), array("invalid" => "Le siren doit être un nombre à 14 chiffres")));
     
        $formChais = new CompteChaisCollectionForm($this->getObject(), array(), array(
	    	'nbChais'    => $this->getOption('nbChais', 1)));
        $this->embedForm('chais', $formChais);
        
    }
}