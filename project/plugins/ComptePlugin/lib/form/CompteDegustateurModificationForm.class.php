<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of CompteDegustateurModificationForm
 *
 * @author mathurin
 */
class CompteDegustateurModificationForm extends CompteModificationForm {
    
    protected $produits;


    public function configure() {
        parent::configure();
        
        $this->setWidget("produits", new sfWidgetFormChoice(array('multiple' => true, 'choices' => $this->getAllProduits())));
        $this->setValidator('produits', new sfValidatorChoice(array('required' => false, 'multiple' => true, 'choices' => array_keys($this->getAllProduits()))));
    }
    
        private function getAllProduits() {
        if (!$this->produits) {
            foreach (ConfigurationClient::getConfiguration()->getProduits() as $hash => $produitCepage) {
                $this->produits[str_replace('/', '-', $hash)] = $produitCepage->getLibelleComplet();
            }
        }
        return $this->produits;
    }
    
        public function save($con = null) {
        if ($produits = $this->values['produits']) {
            $this->getObject()->updateTagsProduits($produits);
        }
        parent::save($con);
    }
    
        private function initDefaultProduits() {
        $default_produits = array();
        foreach ($this->getObject()->getProduits() as $produit_code => $produit) {
            $default_produits[] = $produit_code;
        }
        $this->widgetSchema['produits']->setDefault($default_produits);
    }
}