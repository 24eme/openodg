<?php
/**
 * Model for DRev
 *
 */

class DRev extends BaseDRev 
{
    const CUVE = 'cuve_';
    const BOUTEILLE = 'bouteille_';

	const CUVE_ALSACE = 'cuve_ALSACE';
    const CUVE_GRDCRU = 'cuve_GRDCRU';
    const CUVE_VTSGN = 'cuve_vtsgn';
    const BOUTEILLE_ALSACE = 'bouteille_ALSACE';
    const BOUTEILLE_GRDCRU = 'bouteille_GRDCRU';
	const BOUTEILLE_VTSGN = 'bouteille_vtsgn';

    public static $prelevement_keys = array(
        self::CUVE_ALSACE,
        self::CUVE_GRDCRU,
        self::CUVE_VTSGN,
        self::BOUTEILLE_ALSACE,
        self::BOUTEILLE_GRDCRU,
        self::BOUTEILLE_VTSGN,
    );
	
    public function constructId() 
    {
        $this->set('_id', 'DREV-' . $this->identifiant . '-' . $this->campagne);
    }
    
	public function getConfiguration() 
	{     

        return acCouchdbManager::getClient('Configuration')->retrieveConfiguration('2013');
	}

    public function getConfigProduits() {

        return $this->getConfiguration()->declaration->getProduitsFilter(_ConfigurationDeclaration::TYPE_DECLARATION_DREV_REVENDICATION, "ConfigurationCouleur");
    }

    public function getConfigProduitsLots() {

        return $this->getConfiguration()->declaration->getProduitsFilter(_ConfigurationDeclaration::TYPE_DECLARATION_DREV_LOTS);
    }
	
	public function initDrev($identifiant, $campagne)
	{
        $this->identifiant = $identifiant;
        $this->campagne = $campagne;
	}

    public function initProduits() 
    {
    	$produits = $this->getConfigProduits();
    	foreach ($produits as $produit) {
    		$this->addProduit($produit->getHash());
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

            $this->addLotProduit($hash, self::CUVE);
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
        $produit->getLibelle();

        return $produit;
    }

    public function getPrelevementKeys() {

        return self::$prelevement_keys;
    }

    public function initLots() 
    {
    	$this->prelevements->add(self::CUVE_ALSACE)->getConfigProduitsLots()->initLots();
    }

    public function addPrelevement($key)
    {
        if(!in_array($key, $this->getPrelevementKeys())) {
            
            return null;
        }

        return $this->prelevements->add($key);
    }
    
    public function addLotProduit($hash, $prefix)
    {
        $hash = $this->getConfiguration()->get($hash)->getHashRelation('lots');
        $key = $prefix.$this->getPrelevementsKeyByHash($hash);

        $prelevement = $this->addPrelevement($key);
    	
		$lot = $prelevement->lots->add(str_replace('/', '_', $hash));
        $lot->hash_produit = $hash;
        $lot->getLibelle();
        $lot->remove('no_vtsgn', 1);

        if(!$lot->getConfig()->hasVtsgn()) {
            $lot->add('no_vtsgn', 1);
        }
    }

    public function getPrelevementsKeyByHash($hash) {
        
        return str_replace("appellation_", "", $this->getConfiguration()->get($hash)->getAppellation()->getKey());
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
    
    public function hasLots($vtsgn = false, $horsvtsgn = false)
    {
        foreach($this->prelevements as $prelevement) {
            if ($prelevement->hasLots($vtsgn, $horsvtsgn)) {
                
                return true;
            }
        }

    	return false;
    }

}