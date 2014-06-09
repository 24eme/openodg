<?php
/**
 * Model for DRev
 *
 */

class DRev extends BaseDRev 
{

    public function constructId() 
    {
        $this->set('_id', 'DREV-' . $this->identifiant . '-' . $this->campagne);
    }
    
	public function getConfiguration() 
	{     
        $conf_2013 = acCouchdbManager::getClient('Configuration')->retrieveConfiguration('2013');
        return $conf_2013;
	}
	
	public function initDrev($identifiant, $campagne)
	{
        $this->identifiant = $identifiant;
        $this->campagne = $campagne;
	}

    public function initProduits() 
    {
    	$produits = $this->getConfiguration()->getDrevProduits();
    	foreach ($produits as $produit) {
    		$this->addProduit($produit);
    	}
    }
    
	public function addProduit($hash) 
	{
        $produit = $this->getOrAdd($hash);
        $config = $produit->getConfig();
        $produit->libelle = $config->getLibelle();
        $produit->getLieu()->libelle = $config->getLieu()->libelle;
        $produit->getMention()->libelle = $config->getMention()->libelle;
        $produit->getAppellation()->libelle = $config->getAppellation()->libelle;
        $produit->vtsgn_inclus = ($config->exist('vtsgn_inclus') && $config->get('vtsgn_inclus'))? 1 : 0;
        $produit->actif = 0;
        return $produit;
    }

}