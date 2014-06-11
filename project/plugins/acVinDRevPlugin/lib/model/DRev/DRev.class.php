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

    public function initFromCSV($csv) {
        foreach($csv as $line) {
            if(!preg_match("/^TOTAL/", $line[DRCsvFile::CSV_LIEU]) && !preg_match("/^TOTAL/", $line[DRCsvFile::CSV_CEPAGE])) {

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
        $produit->actif = 0;
        return $produit;
    }

}