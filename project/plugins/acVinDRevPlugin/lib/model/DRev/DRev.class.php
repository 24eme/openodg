<?php
/**
 * Model for DRev
 *
 */

class DRev extends BaseDRev 
{
	const PRODUITS_LOT_ALSACE_CONFIGURATION_KEY = 'ALSACE';
	const PRODUITS_LOT_GRDCRU_CONFIGURATION_KEY = 'GRDCRU';
	const PREFIXE_LOT_KEY = 'cuve_';
	const NODE_CUVE_ALSACE = 'cuve_ALSACE';
	const NODE_CUVE_GRDCRU = 'cuve_GRDCRU';
	
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

    public function initFromCSV($csv) {
        foreach($csv as $line) {
            if(!preg_match("/^TOTAL/", $line[DRCsvFile::CSV_LIEU]) && !preg_match("/^TOTAL/", $line[DRCsvFile::CSV_CEPAGE])) {

                continue;
            }

            if(!$this->getConfiguration()->exist(preg_replace('|/recolte.|', '/declaration/', $line[DRCsvFile::CSV_HASH_PRODUIT]))) {
                
                continue;
            }

            $config = $this->getConfiguration()->get($line[DRCsvFile::CSV_HASH_PRODUIT]);

            if($config instanceof ConfigurationCouleur && $config->getAppellation()->mention->lieu->hasManyCouleur()) {
                $config = $config->getAppellation()->mention->lieu->get($config->getKey());
            } elseif($config instanceof ConfigurationAppellation && !$config->mention->lieu->hasManyCouleur()) {
                $config = $config->mention->lieu->couleur;
            } else {
                continue;
            }

            $produit = $this->get($config->getHash());
            $produit->dr->volume_sur_place += (float) $line[DRCsvFile::CSV_VOLUME];
            if($produit->dr->volume_sur_place_revendique >= 0) {
                $produit->dr->volume_sur_place_revendique += (float) $line[DRCsvFile::CSV_VOLUME] - $line[DRCsvFile::CSV_USAGES_INDUSTRIELS];
            }
            $produit->dr->volume_total += (float) $line[DRCsvFile::CSV_VOLUME_TOTAL];
            $produit->dr->usages_industriels_total += (float) $line[DRCsvFile::CSV_USAGES_INDUSTRIELS_TOTAL];
            $produit->dr->superficie_total += (float) $line[DRCsvFile::CSV_SUPERFICIE_TOTALE];

            if($line[DRCsvFile::CSV_USAGES_INDUSTRIELS] == "") {
                $produit->dr->volume_sur_place_revendique = -1;
            }


        }
    }

    public function updateFromDR() {
        foreach($this->declaration->getProduits() as $produit) {
            $produit->updateFromDR();
        }
    }
    
	public function addProduit($hash)
	{
        $config = $this->getConfiguration()->get($hash);

        $produit = $this->getOrAdd($config->getHash());
        $produit->libelle = $config->getLibelle();
        $produit->getLieu()->libelle = $config->getLieu()->libelle;
        $produit->getMention()->libelle = $config->getMention()->libelle;
        $produit->getAppellation()->libelle = $config->getAppellation()->libelle;
        return $produit;
    }

    public function initLots() 
    {
    	$alsaceProduits = $this->getConfiguration()->getDrevLotProduits(self::PRODUITS_LOT_ALSACE_CONFIGURATION_KEY);
    	$grdCruProduits = $this->getConfiguration()->getDrevLotProduits(self::PRODUITS_LOT_GRDCRU_CONFIGURATION_KEY);
    	foreach ($alsaceProduits as $alsaceProduit) {
    		$this->addLotProduit($this->lots->add(self::PREFIXE_LOT_KEY.self::PRODUITS_LOT_ALSACE_CONFIGURATION_KEY), $alsaceProduit);
    	}
    	foreach ($grdCruProduits as $grdCruProduit) {
    		if (preg_match('/\/lieu\//', $grdCruProduit)) {
    			continue;
    		}
    		$this->addLotProduit($this->lots->add(self::PREFIXE_LOT_KEY.self::PRODUITS_LOT_GRDCRU_CONFIGURATION_KEY), $grdCruProduit);
    	}
    	return $lot;
    }
    
    public function addLotProduit($lot, $produit)
    {
    	$configuration = $this->getConfiguration();
		$cepage = $lot->produits->add(str_replace('/', '_', $produit));
    	$cepage->hash = $produit;
    	$libelle = '';
    	if ($configuration->get($produit)->getLieu()->libelle) {
    		$libelle .= $configuration->get($produit)->getLieu()->libelle.' - ';
    	}
    	$libelle .= $configuration->get($produit)->libelle;
    	$cepage->libelle = $libelle;
    }
    
    public function hasRevendicationAlsace()
    {
    	return 
    		$this->declaration->certification->genre->appellation_ALSACEBLANC->mention->lieu->couleur->isActive() &&
    		$this->declaration->certification->genre->appellation_PINOTNOIR->mention->lieu->couleur->isActive() &&
    		$this->declaration->certification->genre->appellation_PINOTNOIRROUGE->mention->lieu->couleur->isActive() &&
    		$this->declaration->certification->genre->appellation_COMMUNALE->mention->lieu->couleurBlanc->isActive() &&
    		$this->declaration->certification->genre->appellation_COMMUNALE->mention->lieu->couleurRouge->isActive() && 
    		$this->declaration->certification->genre->appellation_LIEUDIT->mention->lieu->couleurBlanc->isActive() &&
    		$this->declaration->certification->genre->appellation_LIEUDIT->mention->lieu->couleurRouge->isActive();
    }
    
    public function hasRevendicationGrdCru()
    {
    	return $this->declaration->certification->genre->appellation_GRDCRU->mention->lieu->couleur->isActive();
    }
    
    public function hasLots($cuve = null, $vtsgn = false, $horsvtsgn = false)
    {
    	if ($cuve && $this->lots->exist($cuve)) {
    		foreach ($this->lots->get($cuve)->produits as $produit) {
    			if ($produit->hasLots($vtsgn, $horsvtsgn)) {
    				return true;
    			}
    		}
    	} else {
    		foreach ($this->lots as $lot) {
	    		foreach ($lot->produits as $produit) {
	    			if ($produit->hasLots($vtsgn, $horsvtsgn)) {
	    				return true;
	    			}
	    		}
    		}
    	}
    	return false;
    }

}