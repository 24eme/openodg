<?php
/**
 * Model for DRev
 *
 */

class DRev extends BaseDRev implements InterfaceProduitsDocument, InterfaceDeclarantDocument
{
    const CUVE = 'cuve_';
    const BOUTEILLE = 'bouteille_';

	const CUVE_ALSACE = 'cuve_ALSACE';
    const CUVE_GRDCRU = 'cuve_GRDCRU';
    const CUVE_VTSGN = 'cuve_vtsgn';
    const BOUTEILLE_ALSACE = 'bouteille_ALSACE';
    const BOUTEILLE_GRDCRU = 'bouteille_GRDCRU';
	const BOUTEILLE_VTSGN = 'bouteille_vtsgn';

    public static $prelevement_libelles = array(
            self::CUVE => "Dégustation conseil",
            self::BOUTEILLE => "Contrôle externe",
    );

    public static $prelevement_libelles_produit_type = array(
            self::CUVE => "Cuve ou fût",
            self::BOUTEILLE => "Bouteille",
    );
    
    public static $prelevement_keys = array(
        self::CUVE_ALSACE,
        self::CUVE_GRDCRU,
        self::CUVE_VTSGN,
        self::BOUTEILLE_ALSACE,
        self::BOUTEILLE_GRDCRU,
        self::BOUTEILLE_VTSGN,
    );

    protected $declarant_document = null;


    public function  __construct() {
        parent::__construct();   
        $this->initDocuments();
    }

    public function __clone() {
        parent::__clone();
        $this->initDocuments();
    } 

    protected function initDocuments() {
        $this->declarant_document = new DeclarantDocument($this);
    }
	
    public function constructId() 
    {
        $this->set('_id', 'DREV-' . $this->identifiant . '-' . $this->campagne);
    }
    
	public function getConfiguration() 
	{     

        return acCouchdbManager::getClient('Configuration')->retrieveConfiguration('2013');
	}

    public function getProduits() {

        return $this->declaration->getProduits();
    }

    public function getConfigProduits() {

        return $this->getConfiguration()->declaration->getProduitsFilter(_ConfigurationDeclaration::TYPE_DECLARATION_DREV_REVENDICATION, "ConfigurationCouleur");
    }

    public function getConfigProduitsLots() {

        return $this->getConfiguration()->declaration->getProduitsFilter(_ConfigurationDeclaration::TYPE_DECLARATION_DREV_LOTS);
    }
	
	public function initDoc($identifiant, $campagne)
	{
        $this->identifiant = $identifiant;
        $this->campagne = $campagne;
        $this->declaration->add('certification')->add('genre');
	}

    public function getCSV() {
       $csv = new DRCsvFile($this->getAttachmentUri('DR.csv'));
       return $csv->getCsvAcheteur($this->identifiant); 
    }

    public function updateFromCSV() {
        $csv = $this->getCSV();
        $this->resetDetail();
        $this->updateDetailFromCSV($csv);
        $this->updateDetail();
        $this->updateRevendiqueFromDetail();
        $this->resetCepage();
        $this->updateCepageFromCSV($csv);
        $this->updateLotsFromCepage();
    }

    public function updateFromDRev($drev) {
        foreach($drev->getProduits() as $produit) {
            $p = $this->addProduit($produit->getHash());
            $p->superficie_revendique = $produit->superficie_revendique;
        }

        foreach($drev->prelevements as $prelevement) {
            $p = $this->addPrelevement($prelevement->getKey());
            foreach($prelevement->lots as $lot) {
                $p->addLotProduit($lot->hash_produit);
            }
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

        return $lot;
    }

    public function getPrelevementsKeyByHash($hash) {
        
        return str_replace("appellation_", "", $this->getConfiguration()->get($hash)->getAppellation()->getKey());
    }

    public function getPrelevementsByDate($filter_key = null) {
        $prelevements = array();
        foreach($this->prelevements as $prelevement) {
            if(!$prelevement->date) {
                
                continue;
            }
            if($filter_key && !preg_match("/".$filter_key."/", $prelevement->getKey())) {

                continue;
            }
            $prelevements[$prelevement->date.$prelevement->getKey()] = $prelevement;
        }

        //ksort($prelevements);

        return $prelevements;
    }
    
    public function hasRevendicationAlsace()
    {
    	return 
    		$this->declaration->certification->genre->appellation_ALSACEBLANC->mention->lieu->couleur->isActive() ||
    		$this->declaration->certification->genre->appellation_PINOTNOIR->mention->lieu->couleur->isActive() ||
    		$this->declaration->certification->genre->appellation_PINOTNOIRROUGE->mention->lieu->couleur->isActive() ||
    		$this->declaration->certification->genre->appellation_COMMUNALE->mention->lieu->couleurBlanc->isActive() ||
    		$this->declaration->certification->genre->appellation_COMMUNALE->mention->lieu->couleurRouge->isActive() || 
    		$this->declaration->certification->genre->appellation_LIEUDIT->mention->lieu->couleurBlanc->isActive() ||
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

    public function storeDeclarant() {
        $this->declarant_document->storeDeclarant();
    }

    public function validate() {
        $this->validation = date('Y-m-d');
    }

    public function getEtablissementObject() {

        return EtablissementClient::getInstance()->findByIdentifiant($this->identifiant);
    }

    public function initProduits() 
    {
        $produits = $this->getConfigProduits();
        foreach ($produits as $produit) {
            $this->addProduit($produit->getHash());
        }
    }

    protected function updateDetailFromCSV($csv) {
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

            $produit = $this->addProduit($config->getHash());
            $produit->detail->volume_total += (float) $line[DRCsvFile::CSV_VOLUME_TOTAL];
            $produit->detail->usages_industriels_total += (float) $line[DRCsvFile::CSV_USAGES_INDUSTRIELS_TOTAL];
            $produit->detail->superficie_total += (float) $line[DRCsvFile::CSV_SUPERFICIE_TOTALE];
            $produit->detail->volume_sur_place += (float) $line[DRCsvFile::CSV_VOLUME];
            if($line[DRCsvFile::CSV_USAGES_INDUSTRIELS] == "") {
                $produit->detail->usages_industriels_sur_place = -1;
            } elseif($produit->detail->usages_industriels_sur_place != -1) {
                $produit->detail->usages_industriels_sur_place += (float) $line[DRCsvFile::CSV_USAGES_INDUSTRIELS];
            }
        }
    }

    protected function resetDetail() {
        foreach($this->declaration->getProduits() as $produit) {
            $produit->resetDetail();
        }
    }

    protected function updateDetail() {
        foreach($this->declaration->getProduits() as $produit) {
            $produit->updateDetail();
        }
    }

    protected function updateRevendiqueFromDetail() {
        foreach($this->declaration->getProduits() as $produit) {
            $produit->updateRevendiqueFromDetail();
        }
    }

    protected function updateCepageFromCSV($csv) {
        foreach($csv as $line) {
            if(
               preg_match("/^TOTAL/", $line[DRCsvFile::CSV_APPELLATION]) ||
               preg_match("/^TOTAL/", $line[DRCsvFile::CSV_LIEU]) ||
               preg_match("/^TOTAL/", $line[DRCsvFile::CSV_CEPAGE])
               ) {

                continue;
            }

            $hash = preg_replace("|/detail/.+$|", "", preg_replace('|/recolte.|', '/declaration/', preg_replace("|/detail/[0-9]+$|", "", $line[DRCsvFile::CSV_HASH_PRODUIT])));
            
            if(!$this->getConfiguration()->exist($hash)) {
                continue;
            }

            $config = $this->getConfiguration()->get($hash);

            $produit = $this->getOrAdd($config->getHash());
            if($line[DRCsvFile::CSV_VTSGN]) {
                $produit->volume_revendique_vtsgn += (float) $line[DRCsvFile::CSV_VOLUME];
            } else {
                $produit->volume_revendique += (float) $line[DRCsvFile::CSV_VOLUME];
            }
            $produit->superficie_revendique += (float) $line[DRCsvFile::CSV_SUPERFICIE_TOTALE];
        }

    }

    protected function updateLotsFromCepage() {
        foreach($this->declaration->getProduitsCepage() as $produit) {
            $this->addLotProduit($produit->getHash(), self::CUVE);
        }
    }

    protected function resetCepage() {
        foreach($this->declaration->getProduitsCepage() as $produit) {
            $produit->resetRevendique();
        }
    }

}