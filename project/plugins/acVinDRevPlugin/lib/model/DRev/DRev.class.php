<?php
/**
 * Model for DRev
 *
 */

class DRev extends BaseDRev 
{
	const PRODUITS_LOT_ALSACE_CONFIGURATION_KEY = 'ALSACE';
	const PREFIXE_LOT_KEY = 'cuve_';
	
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
        $produit->actif = 0;
        return $produit;
    }

    public function initLots() 
    {
    	$produits = $this->getConfiguration()->getDrevLotProduits(self::PRODUITS_LOT_ALSACE_CONFIGURATION_KEY);
    	$lotKey = self::PREFIXE_LOT_KEY.self::PRODUITS_LOT_ALSACE_CONFIGURATION_KEY;
    	$lot = $this->lots->add($lotKey);
    	$configuration = $this->getConfiguration();
    	foreach ($produits as $produit) {
    		$cepage = $lot->produits->add(str_replace('/', '_', $produit));
    		$cepage->hash = $produit;
    		$cepage->libelle = $configuration->get($produit)->libelle;
    	}
    	return $lot;
    }

}