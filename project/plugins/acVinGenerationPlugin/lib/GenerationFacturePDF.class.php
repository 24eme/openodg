<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of class GenerationFacturePDF
 * @author mathurin
 */
class GenerationFacturePDF extends GenerationPDF {
    
    function __construct(Generation $g, $config = null, $options = null) {
        parent::__construct($g, $config, $options);
    }

    public function preGeneratePDF() {
        parent::preGeneratePDF();

        $template = TemplateFactureClient::getInstance()->find($this->generation->arguments->modele);

        if(!$template) {
            throw new sfException(sprintf("Le template de facture %s n'existe pas", $this->generation->arguments->modele));
        }

        $comptes_id = FactureClient::getInstance()->getComptesIdFilterWithParameters($this->generation->arguments->toArray());

        $message_communication = (array_key_exists('message_communication', $arguments))? $arguments['message_communication'] : null;
        
        if(!$this->generation->exist('somme')) {
          $this->generation->somme = 0;
        }
        
        $cpt = count($this->generation->documents);

        foreach($comptes_id as $compte_id) {
            $compte = CompteClient::getInstance()->find($compte_id);

            if(!$compte) {
                throw new sfException(sprintf("Compte inexistant %s", $compte_id));
            }

            if(!$compte->cvi) {
                throw new sfException(sprintf("Le compte %s n'a pas de numéro CVI", $compte_id));
            }

            $cotisations = $template->generateCotisations($compte->cvi, $template->campagne);
            $facture = FactureClient::getInstance()->createDoc($cotisations, $compte, $arguments['date_facturation'],$message_communication);
            $facture->save();
            $this->generation->somme += $facture->total_ttc;
            $this->generation->documents->add($cpt, $facture->_id);
            $this->generation->save();
            $cpt++;
        }
    }
    
    /*public function preGeneratePDF() {
       parent::preGeneratePDF();     
       $regions = explode(',',$this->generation->arguments->regions);
       $allMouvementsByRegion = FactureClient::getInstance()->getMouvementsForMasse($regions); 
       $mouvementsBySoc = FactureClient::getInstance()->getMouvementsNonFacturesBySoc($allMouvementsByRegion);
       $arguments = $this->generation->arguments->toArray();
       $mouvementsBySoc = FactureClient::getInstance()->filterWithParameters($mouvementsBySoc,$arguments);
       $message_communication = (array_key_exists('message_communication', $arguments))? $arguments['message_communication'] : null;
       if(!$this->generation->exist('somme')) $this->generation->somme = 0;
       $cpt = count($this->generation->documents);
       foreach ($mouvementsBySoc as $societeID => $mouvementsSoc) {
	 $societe = SocieteClient::getInstance()->find($societeID);
	 if (!$societe)
	   throw new sfException($societeID." unknown :(");
	 $facture = FactureClient::getInstance()->createDoc($mouvementsSoc, $societe, $arguments['date_facturation'],$message_communication);
         $facture->save();
         $this->generation->somme += $facture->total_ttc;
         $this->generation->documents->add($cpt,$facture->_id);
         $cpt++;
        }
    }*/
    
    protected function generatePDFForADocumentId($factureid) {
      $facture = FactureClient::getInstance()->find($factureid);
      if (!$facture) {
	throw new sfException("Facture $factureid doesn't exist\n");
      }
      return new FactureLatex($facture, $this->config);
    }

    protected function getDocumentName() {
      return "Factures";
    }

}
