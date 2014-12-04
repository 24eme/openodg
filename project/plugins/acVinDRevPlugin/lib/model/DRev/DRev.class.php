<?php

/**
 * Model for DRev
 *
 */
class DRev extends BaseDRev implements InterfaceProduitsDocument, InterfaceDeclarantDocument {

    const CUVE = 'cuve_';
    const BOUTEILLE = 'bouteille_';
    const CUVE_ALSACE = 'cuve_ALSACE';
    const CUVE_GRDCRU = 'cuve_GRDCRU';
    const CUVE_VTSGN = 'cuve_VTSGN';
    const BOUTEILLE_ALSACE = 'bouteille_ALSACE';
    const BOUTEILLE_GRDCRU = 'bouteille_GRDCRU';
    const BOUTEILLE_VTSGN = 'bouteille_VTSGN';

    public static $prelevement_libelles = array(
        self::CUVE => "Dégustation conseil",
        self::BOUTEILLE => "Contrôle externe",
    );
    public static $prelevement_libelles_produit_type = array(
        self::CUVE => "Cuve ou fût",
        self::CUVE_VTSGN => "Cuve, fût ou bouteille",
        self::BOUTEILLE => "Bouteille",
    );
    public static $prelevement_appellation_libelles = array(
        self::CUVE => "Cuve ou fût",
        self::CUVE_VTSGN => "Cuve, fût ou bouteille",
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

    public function __construct() {
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

    public function constructId() {
        $this->set('_id', 'DREV-' . $this->identifiant . '-' . $this->campagne);
    }

    public function getConfiguration() {

        return acCouchdbManager::getClient('Configuration')->retrieveConfiguration($this->campagne);
    }

    public function getProduits($onlyActive = false) {

        return $this->declaration->getProduits($onlyActive);
    }

    public function getConfigProduits() {

        return $this->getConfiguration()->declaration->getProduitsFilter(_ConfigurationDeclaration::TYPE_DECLARATION_DREV_REVENDICATION, "ConfigurationCouleur");
    }

    public function getConfigProduitsLots() {

        return $this->getConfiguration()->declaration->getProduitsFilter(_ConfigurationDeclaration::TYPE_DECLARATION_DREV_LOTS);
    }

    public function mustDeclareCepage() {

        return $this->isNonRecoltant() || $this->hasDR();
    }

    public function isNonRecoltant() {

        return $this->exist('non_recoltant') && $this->get('non_recoltant');
    }

    public function isNonConditionneur() {

        return $this->exist('non_conditionneur') && $this->get('non_conditionneur');
    }

    public function isPapier() { 
        return $this->exist('papier') && $this->get('papier');
    }

    public function hasDR() {

        return $this->_attachments->exist('DR.csv');
    }

    public function initDoc($identifiant, $campagne) {
        $this->identifiant = $identifiant;
        $this->campagne = $campagne;
        $etablissement = $this->getEtablissementObject();
        $this->declaration->add('certification')->add('genre');
    }

    public function initAppellations() {
        foreach ($this->declaration->certification->genre->getConfigChidrenNode() as $appellation) {
            $this->addAppellation($appellation->getHash());
        }
    }

    public function getCSV() {
        $csv = new DRCsvFile($this->getAttachmentUri('DR.csv'));
        return $csv->getCsvAcheteur($this->identifiant);
    }

    public function updateFromCSV() {
        $csv = $this->getCSV();
        $this->updatePrelevementsFromRevendication();
        $this->resetDetail();
        $this->updateDetailFromCSV($csv);
        $this->updateDetail();
        $this->updateRevendiqueFromDetail();
        $this->resetCepage();
        $this->updateCepageFromCSV($csv);
        $this->updatePrelevementsFromRevendication();
        $this->updateLotsFromCepage();
        $this->declaration->reorderByConf();
    }

    public function reUpdateCepageFromCSV() {
        $csv = $this->getCSV();
        $this->resetCepage();
        $this->updateCepageFromCSV($csv);
    }

    public function updateFromDRev($drev) {
        foreach ($drev->getProduits() as $produit) {
            $this->addAppellation($produit->getAppellation()->getHash());
            if(!$produit->superficie_revendique && !$produit->volume_revendique) {
                continue;
            }
            $p = $this->addProduit($produit->getHash());
            $p->superficie_revendique = $produit->superficie_revendique;
        }

        if ($drev->prelevements->exist(self::CUVE_ALSACE) && count($drev->prelevements->get(self::CUVE_ALSACE)->lots) > 0) {
            foreach ($drev->getProduits() as $produit) {
                $hash_rev_lot = $drev->getConfiguration()->get($produit->getHash())->getHashRelation('lots');

                foreach ($drev->prelevements->get(self::CUVE_ALSACE)->lots as $lot) {

                    $this->addLotProduit($lot->hash_produit, self::CUVE);

                    if (!preg_match("|" . $hash_rev_lot . "|", $lot->hash_produit)) {

                        continue;
                    }

                    $hash = str_replace($hash_rev_lot, $produit->getHash(), $lot->hash_produit);

                    if (!$drev->getConfiguration()->exist($hash)) {

                        continue;
                    }

                    if ($drev->getConfiguration()->get($hash)->getAppellation()->hasManyLieu()) {

                        continue;
                    }

                    if ($drev->getConfiguration()->get($hash)->getAppellation()->hasLieuEditable()) {

                        continue;
                    }

                    $this->getOrAdd($hash)->addDetailNode();
                }
            }
        }

        if ($drev->prelevements->exist(self::CUVE_GRDCRU)) {
            foreach ($drev->prelevements->get(self::CUVE_GRDCRU)->lots as $lot) {
                if (!$drev->getConfiguration()->exist($lot->hash_produit)) {

                    continue;
                }

                $this->addLotProduit($lot->hash_produit, self::CUVE);

                $this->getOrAdd($lot->hash_produit)->addDetailNode();
            }
        }

        $this->declaration->reorderByConf();
    }

    public function addAppellation($hash) {
        $config = $this->getConfiguration()->get($hash);
        $appellation = $this->getOrAdd($config->hash);
        $config_produits = $appellation->getConfigProduits();
        if (count($config_produits) == 1) {
            reset($config_produits);
            $this->addProduitCepage(key($config_produits), null, false);
        } else {
            foreach($config_produits as $hash => $config_produit) {
                if($config_produit->isAutoDRev()) {
                    $this->addProduitCepage($hash, null, false);
                }
            }
        }

        return $appellation;
    }

    public function addProduit($hash, $add_appellation = true) {
        $config = $this->getConfiguration()->get($hash);
        if($add_appellation) {
            $this->addAppellation($config->getAppellation()->getHash());
        }
        $produit = $this->getOrAdd($config->getHash());
        $produit->getLibelle();

        return $produit;
    }

    public function addProduitCepage($hash, $lieu = null, $add_appellation = true) {
        $produit = $this->getOrAdd($hash);

        $this->addProduit($produit->getProduitHash(), $add_appellation);

        return $produit->addDetailNode($lieu);
    }

    public function cleanDoc() {

        $this->declaration->cleanNode();
        $this->cleanLots();
    }

    public function cleanLots() {
        foreach($this->prelevements as $prelevement) {
            $prelevement->cleanLots();
        }
    }

    public function getPrelevementKeys() {

        return self::$prelevement_keys;
    }

    public function initLots() {
        $this->prelevements->add(self::CUVE_ALSACE)->getConfigProduitsLots()->initLots();
    }

    public function hasPrelevement($key) {

        return $this->prelevements->exist($key);
    }

    public function addPrelevement($key) {
        if (!in_array($key, $this->getPrelevementKeys())) {

            return null;
        }

        $prelevement = $this->prelevements->add($key);

        if (!$this->chais->exist($prelevement->getPrefix())) {
            $chai = $this->getEtablissementObject()->getChaiDefault();
            if ($chai) {
                $this->chais->add($prelevement->getPrefix(), $chai->toArray(true, false));
            }
        }

        return $this->prelevements->add($key);
    }

    public function addLotProduit($hash, $prefix) {
        $hash = $this->getConfiguration()->get($hash)->getHashRelation('lots');
        $key = $prefix . $this->getPrelevementsKeyByHash($hash);

        $prelevement = $this->addPrelevement($key);

        if (!$prelevement) {

            return;
        }

        $lot = $prelevement->lots->add(str_replace('/', '_', $hash));
        $lot->hash_produit = $hash;
        $lot->getLibelle();
        $lot->remove('no_vtsgn');

        if (!$lot->getConfig()->hasVtsgn()) {
            $lot->add('no_vtsgn', 1);
        }

        return $lot;
    }

    public function getPrelevementsKeyByHash($hash) {

        return str_replace("appellation_", "", $this->getConfiguration()->get($hash)->getAppellation()->getKey());
    }

    public function getPrelevementsByDate($filter_key = null) {
        $prelevements = array();
        foreach ($this->prelevements as $prelevement) {
            if (!$prelevement->date) {

                continue;
            }
            if ($filter_key && !preg_match("/" . $filter_key . "/", $prelevement->getKey())) {

                continue;
            }
            $prelevements[$prelevement->getKey() . $prelevement->date] = $prelevement;
        }

        krsort($prelevements);

        return $prelevements;
    }

    public function getPrelevementsOrdered($filter_key = null) {
        $drev_prelevements = $this->getPrelevementsByDate();
        $ordrePrelevements = DRevClient::getInstance()->getOrdrePrelevements();
        $result = array();
        foreach ($ordrePrelevements as $type => $prelevementsOrdered) {
            foreach ($prelevementsOrdered as $prelevementOrdered) {
                foreach ($drev_prelevements as $prelevement) {                    
                    if ('/prelevements/' . $prelevementOrdered == $prelevement->getHash()) {

                        if (!array_key_exists($type, $result)) {

                            $result[$type] = new stdClass();
                            if ($type == "cuve") {
                                $result[$type]->libelle = "Dégustation conseil";
                            }
                            if ($type == "bouteille") {
                                $result[$type]->libelle = "Contrôle externe";
                            }
                            $result[$type]->prelevements = array();
                        }
                        $result[$type]->prelevements[] = $prelevement;
                    }
                }
            }
        
        }
        return $result;
    }

    public function hasRevendicationAlsace() {
        return true;
    }

    public function hasRevendicationGrdCru() {
        return $this->declaration->certification->genre->exist('appellation_GRDCRU') && $this->declaration->certification->genre->appellation_GRDCRU->mention->lieu->couleur->isActive();
    }

    public function hasLots($vtsgn = false, $horsvtsgn = false) {
        foreach ($this->prelevements as $prelevement) {
            if ($prelevement->hasLots($vtsgn, $horsvtsgn)) {

                return true;
            }
        }

        return false;
    }

    public function storeDeclarant() {
        $this->declarant_document->storeDeclarant();
    }

    public function storeEtape($etape) {
        $this->add('etape', $etape);
    }

    public function validate($date = null) {
        if(is_null($date)) {
            $date = date('Y-m-d');
        }

        $this->updatePrelevements();
        $this->cleanDoc();
        $this->validation = $date;
    }

    public function validateOdg() {
        $this->validation_odg = date('Y-m-d');
    }

    public function getEtablissementObject() {

        return EtablissementClient::getInstance()->findByIdentifiant($this->identifiant);
    }

    public function initProduits() {
        $produits = $this->getConfigProduits();
        foreach ($produits as $produit) {
            $this->addProduit($produit->getHash());
        }
    }

    protected function updateDetailFromCSV($csv) {
        foreach ($csv as $line) {
            if (!preg_match("/^TOTAL/", $line[DRCsvFile::CSV_LIEU]) && !preg_match("/^TOTAL/", $line[DRCsvFile::CSV_CEPAGE])) {

                continue;
            }

            if (!$this->getConfiguration()->exist(preg_replace('|/recolte.|', '/declaration/', $line[DRCsvFile::CSV_HASH_PRODUIT]))) {

                continue;
            }

            $config = $this->getConfiguration()->get($line[DRCsvFile::CSV_HASH_PRODUIT])->getNodeRelation('revendication');

            if ($config instanceof ConfigurationAppellation && !$config->mention->lieu->hasManyCouleur()) {
                $config = $config->mention->lieu->couleur;
            }

            if (!$config instanceof ConfigurationCouleur) {
                continue;
            }

            $produit = $this->addProduit($config->getHash());
            $produit->detail->volume_total += (float) $line[DRCsvFile::CSV_VOLUME_TOTAL];
            $produit->detail->usages_industriels_total += (float) $line[DRCsvFile::CSV_USAGES_INDUSTRIELS_TOTAL];
            $produit->detail->superficie_total += (float) $line[DRCsvFile::CSV_SUPERFICIE_TOTALE];
            $produit->detail->volume_sur_place += (float) $line[DRCsvFile::CSV_VOLUME];
            if ($line[DRCsvFile::CSV_USAGES_INDUSTRIELS] == "") {
                $produit->detail->usages_industriels_sur_place = -1;
            } elseif ($produit->detail->usages_industriels_sur_place != -1) {
                $produit->detail->usages_industriels_sur_place += (float) $line[DRCsvFile::CSV_USAGES_INDUSTRIELS];
            }
        }
    }

    protected function resetDetail() {
        foreach ($this->declaration->getProduits() as $produit) {
            $produit->resetDetail();
        }
    }

    protected function updateDetail() {
        foreach ($this->declaration->getProduits() as $produit) {
            $produit->updateDetail();
        }
    }

    protected function updateRevendiqueFromDetail() {
        foreach ($this->declaration->getProduits() as $produit) {
            $produit->updateRevendiqueFromDetail();
        }
    }

    public function updatePrelevementsFromRevendication() {
        $prelevements_to_delete = array_flip($this->prelevement_keys);
        foreach ($this->declaration->getProduits() as $produit) {
            if (!$produit->volume_revendique) {

                continue;
            }
            $hash = $this->getConfiguration()->get($produit->getHash())->getHashRelation('lots');
            $key = $this->getPrelevementsKeyByHash($hash);
            $this->addPrelevement(self::CUVE . $key);
            if(!$this->isNonConditionneur()) {
                $this->addPrelevement(self::BOUTEILLE . $key);
            }
            unset($prelevements_to_delete[self::CUVE . $key]);
            unset($prelevements_to_delete[self::BOUTEILLE . $key]);
        }

        if ($this->declaration->hasVtsgn()) {
            $this->addPrelevement(self::CUVE_VTSGN);
            if(!$this->isNonConditionneur()) {
                $this->addPrelevement(self::BOUTEILLE_VTSGN);
            }
            unset($prelevements_to_delete[self::CUVE_VTSGN]);
            unset($prelevements_to_delete[self::BOUTEILLE_VTSGN]);
        }

        foreach ($prelevements_to_delete as $key => $value) {
            if (!$this->prelevements->exist($key)) {

                continue;
            }

            $this->prelevements->remove($key);
        }
    }

    public function updatePrelevements() {
        foreach($this->prelevements as $prelevement) {
            $prelevement->updatePrelevement();
        }
    }

    protected function updateCepageFromCSV($csv) {
        foreach ($csv as $line) {
            if (
                    preg_match("/^TOTAL/", $line[DRCsvFile::CSV_APPELLATION]) ||
                    preg_match("/^TOTAL/", $line[DRCsvFile::CSV_LIEU]) ||
                    preg_match("/^TOTAL/", $line[DRCsvFile::CSV_CEPAGE])
            ) {

                continue;
            }

            $hash = preg_replace("|/detail/.+$|", "", preg_replace('|/recolte.|', '/declaration/', preg_replace("|/detail/[0-9]+$|", "", $line[DRCsvFile::CSV_HASH_PRODUIT])));

            if (!$this->getConfiguration()->exist($hash)) {
                continue;
            }

            $config = $this->getConfiguration()->get($hash);
            $detail = $this->getOrAdd($config->getHash())->addDetailNode($line[DRCsvFile::CSV_LIEU]);
            if ($line[DRCsvFile::CSV_VTSGN] == "VT") {
                $detail->volume_revendique_vt += (float) $line[DRCsvFile::CSV_VOLUME];
                $detail->superficie_revendique_vt += (float) $line[DRCsvFile::CSV_SUPERFICIE_TOTALE];
            } elseif ($line[DRCsvFile::CSV_VTSGN] == "SGN") {
                $detail->volume_revendique_sgn += (float) $line[DRCsvFile::CSV_VOLUME];
                $detail->superficie_revendique_sgn += (float) $line[DRCsvFile::CSV_SUPERFICIE_TOTALE];
            } else {
                $detail->volume_revendique += (float) $line[DRCsvFile::CSV_VOLUME];
                $detail->superficie_revendique += (float) $line[DRCsvFile::CSV_SUPERFICIE_TOTALE];
            }

            $detail->updateTotal();
        }
    }

    public function getProduitsCepageByAppellations() {
        $appellations = $this->declaration->getAppellations();
        $produitsCepageByAppellations = array();
        $nb_cepages = 0;
        foreach ($appellations as $appellation) {
            $produitsCepageByAppellations[$appellation->getHash()] = new stdClass();
            $produitsCepageByAppellations[$appellation->getHash()]->appellation = $appellation;
            $produitsCepageByAppellations[$appellation->getHash()]->cepages = $appellation->getProduitsCepage();
            $nb_cepages += count($appellation->getProduitsCepage());
        }
        if($nb_cepages === 0){
            return null;
        }
        return $produitsCepageByAppellations;
    }

    public function updateLotsFromCepage() {
        $prelevements = array();
        foreach ($this->declaration->getProduitsCepage() as $produit) {
            if(!$produit->volume_revendique > 0) {
                continue;
            }

            $lot = $this->addLotProduit($produit->getCepage()->getHash(), self::CUVE);

            if ($lot) {
                $prelevements[$lot->getPrelevement()->getKey()] = $lot->getPrelevement();
            }
        }

        foreach ($prelevements as $prelevement) {
            $prelevement->reorderByConf();
        }
    }

    protected function resetCepage() {
        foreach ($this->declaration->getProduitsCepage() as $produit) {
            $produit->resetRevendique();
        }
    }

    public function updateProduitsFromCepage() {
        foreach($this->getProduits() as $produit) {
            $produit->updateFromCepage();
        }
    }

    public function getChaiKey($conditionnement) {
        if ($this->exist('chais')) {
            if ($this->chais->exist($conditionnement)) {
                foreach ($this->getEtablissementObject()->chais as $chai) {
                    if ($chai->adresse == $this->chais->get($conditionnement)->adresse) {
                        return $chai->getKey();
                    }
                }
            }
        }
        return null;
    }
    
    public function hasCompleteDocuments()
    {
    	$complete = true;
    	foreach($this->getOrAdd('documents') as $document) {
    		if ($document->statut != DRevDocuments::STATUT_RECU) {
    			$complete = false;
    			break;
    		}
    	}
    	return $complete;
    }

}
