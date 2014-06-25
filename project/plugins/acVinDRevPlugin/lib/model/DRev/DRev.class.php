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
        $this->initFromCSVRevendication($csv);
        $this->initFromCSVLots($csv);
    }

    public function initFromCSVRevendication($csv) {
        foreach($csv as $line) {
            if(!preg_match("/^TOTAL/", $line[DRCsvFile::CSV_LIEU]) && !preg_match("/^TOTAL/", $line[DRCsvFile::CSV_CEPAGE])) {

                continue;
            }

            if(!$this->getConfiguration()->exist(preg_replace('|/recolte.|', '/declaration/', $line[DRCsvFile::CSV_HASH_PRODUIT]))) {
                
                continue;
            }

            $config = $this->getConfiguration()->get($line[DRCsvFile::CSV_HASH_PRODUIT])->getNodeRelation('revendication');

            if($config instanceof ConfigurationAppellation && !$config->mention->lieu->hasManyCouleur()) {
                $config = $config->mention->lieu->couleur;
            }

            if(!$config instanceof ConfigurationCouleur) {
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

    public function initFromCSVLots($csv) {
        foreach($csv as $line) {
            if(
               preg_match("/^TOTAL/", $line[DRCsvFile::CSV_APPELLATION]) ||
               preg_match("/^TOTAL/", $line[DRCsvFile::CSV_LIEU]) ||
               preg_match("/^TOTAL/", $line[DRCsvFile::CSV_CEPAGE])
               ) {

                continue;
            }

            $hash = preg_replace('|/recolte.|', '/declaration/', preg_replace("|/detail/[0-9]+$|", "", $line[DRCsvFile::CSV_HASH_PRODUIT]));

            if(!$this->getConfiguration()->exist($hash)) {
                
                continue;
            }

            $config = $this->getConfiguration()->get($hash);

            if(!$config instanceof ConfigurationCepage) {
                continue;
            }

            $this->addLotProduit($hash);
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
        $produit->actif = 0;
        return $produit;
    }

    public function initLots() 
    {
    	$alsaceProduits = $this->getConfiguration()->getDrevLotProduits(self::PRODUITS_LOT_ALSACE_CONFIGURATION_KEY);
    	$grdCruProduits = $this->getConfiguration()->getDrevLotProduits(self::PRODUITS_LOT_GRDCRU_CONFIGURATION_KEY);
    	
        foreach ($alsaceProduits as $alsaceProduit) {
    		$this->addLotProduit($alsaceProduit);
    	}

    	foreach ($grdCruProduits as $grdCruProduit) {
    		if (preg_match('/\/lieu\//', $grdCruProduit)) {
    			continue;
    		}
    		$this->addLotProduit($grdCruProduit);
    	}
    }
    
    public function addLotProduit($hash, $prefix = self::PREFIXE_LOT_KEY)
    {
        $hash = $this->getConfiguration()->get($hash)->getHashRelation('lots');
        $key = $prefix.$this->getLotsKeyByHash($hash);
        $lot = $this->lots->add($key);
    	$configuration = $this->getConfiguration();
		$cepage = $lot->produits->add(str_replace('/', '_', $hash));
    	$cepage->hash = $hash;
    	$libelle = '';
    	if ($configuration->get($hash)->getLieu()->libelle) {
    		$libelle .= $configuration->get($hash)->getLieu()->libelle.' - ';
    	}
    	$libelle .= $configuration->get($hash)->libelle;
    	$cepage->libelle = $libelle;

        $cepage->remove('no_vtsgn', 1);

        if(!$configuration->get($hash)->hasVtsgn()) {
            $cepage->add('no_vtsgn', 1);
        }
    }

    public function getLotsKeyByHash($hash) {
        
        return str_replace("appellation_", "", $this->getConfiguration()->get($hash)->getAppellation()->getKey());
    }

}