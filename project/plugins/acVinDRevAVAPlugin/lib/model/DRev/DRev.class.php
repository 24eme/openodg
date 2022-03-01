<?php

/**
 * Model for DRev
 *
 */
class DRev extends BaseDRev implements InterfaceProduitsDocument, InterfaceVersionDocument, InterfaceDeclarantDocument, InterfaceDeclaration, InterfaceMouvementFacturesDocument, InterfacePieceDocument {

    const CUVE = 'cuve_';
    const BOUTEILLE = 'bouteille_';
    const CUVE_ALSACE = 'cuve_ALSACE';
    const CUVE_CREMANT = 'cuve_CREMANT';
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
        self::CUVE_CREMANT,
        self::CUVE_GRDCRU,
        self::CUVE_VTSGN,
        self::BOUTEILLE_ALSACE,
        self::BOUTEILLE_GRDCRU,
        self::BOUTEILLE_VTSGN,
    );

    protected $declarant_document = null;
    protected $mouvement_document = null;
    protected $version_document = null;
    protected $piece_document = null;

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
        $this->mouvement_document = new MouvementFacturesDocument($this);
        $this->version_document = new VersionDocument($this);
        $this->piece_document = new PieceDocument($this);
    }

    public function constructId() {
        $id = 'DREV-' . $this->identifiant . '-' . $this->campagne;
        if($this->version) {
            $id .= "-".$this->version;
        }
        $this->set('_id', $id);
    }

    public function getConfiguration() {

        return acCouchdbManager::getClient('Configuration')->retrieveConfiguration($this->campagne);
    }

    public function getConfigurationPrecedente() {

        return acCouchdbManager::getClient('Configuration')->retrieveConfiguration(($this->campagne - 1).'');
    }

    public function getProduits($onlyActive = false) {

        return $this->declaration->getProduits($onlyActive);
    }

    public function getProduitsVCI() {
    	return $this->declaration->getProduitsVCI();
    }

    public function calculateVolumeRevendiqueVCI()
    {
    	$vci = array();
    	$vciChezNegociant = array();
        foreach($this->getProduits() as $produit) {
            if(!$produit->exist('volume_revendique_vci') || $this->isNonRecoltant()) {
                continue;
            }
            $produit->volume_revendique_vci = 0;
        }
    	foreach ($this->getProduitsVci() as $produit) {
            if($this->isRecoltant() && !$produit->isStockageCaveParticuliere() && !$produit->isStockageNegociant()) {
                continue;
            }
    		if (!isset($vci[$produit->getCouleur()->getHash()])) {

                $vci[$produit->getCouleur()->getHash()] = 0;
                $vciChezNegociant[$produit->getCouleur()->getHash()] = 0;
    		}
            $vciProduit = $produit->complement + $produit->substitution + $produit->rafraichi;
            $vci[$produit->getCouleur()->getHash()] += $vciProduit;
            if($produit->isStockageNegociant()) {
                $vciChezNegociant[$produit->getCouleur()->getHash()] += $vciProduit;
            }
    	}
    	foreach ($vci as $hash => $val) {
            $this->get($hash)->add('volume_revendique_vci');
            $this->get($hash)->setVolumeRevendiqueVCI($val, $vciChezNegociant[$hash]);
    	}
    }

    public function isRecoltant() {

        return !$this->isNonRecoltant();
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

    public function isLectureSeule() {

        return $this->exist('lecture_seule') && $this->get('lecture_seule');
    }

    public function isNonVinificateur() {

        return $this->exist('non_vinificateur') && $this->get('non_vinificateur');
    }

    public function isNonConditionneurJustForThisMillesime() {

        return $this->isNonConditionneur() && $this->chais->exist(Drev::BOUTEILLE);
    }

    public function isPapier() {

        return $this->exist('papier') && $this->get('papier');
    }

    public function isAutomatique() {

        return $this->exist('automatique') && $this->get('automatique');
    }

    public function getValidation() {

        return $this->_get('validation');
    }

    public function getValidationOdg() {

        return $this->_get('validation_odg');
    }

    public function hasDR() {

        return $this->_attachments->exist('DR.csv');
    }

    public function getLastRegistreVCI() {
      return RegistreVCIClient::getInstance()->findMasterByIdentifiantAndCampagne($this->identifiant, ($this->campagne -1));
    }

    public function initDoc($identifiant, $campagne) {
        $this->identifiant = $identifiant;
        $this->campagne = $campagne;
        $etablissement = $this->getEtablissementObject();
        $this->declaration->add('certification')->add('genre');
    }

    public function populateVCIFromRegistre()
    {
    	if ($registre = $this->getLastRegistreVCI()) {
    		foreach($registre->declaration as $key => $p) {
    			$hash = $p->getHash();
    			if (!preg_match('/\/couleur\//', $hash)) {
    				$hash .= '/mention/lieu/couleur';
    			}
                if (!preg_match('/\/cepage_/', $hash)) {
                    $node = $this->addProduit($hash);
                } else {
                    $node = $this->addProduitCepage($hash);
                }
    			if (!$node->exist('vci') && $node->getDefinition()->exist('vci')) {
    				$node->add('vci', $p->details);
    			}
    		}
    	}

        $this->declaration->reorderByConf();
    }

    public function hasProduitsVCI()
    {
    	foreach ($this->declaration->getProduitsCepage() as $h => $p) {
    		if ($p->getConfig()->hasRendementVCI()) {
    			return true;
    		}
    	}
    	return false;
    }

    public function initAppellations() {
        foreach ($this->declaration->certification->genre->getConfigChidrenNode() as $appellation) {
            $this->addAppellation($appellation->getHash());
        }
    }

    public function getAttachmentUri($filename) {
        if(!$this->exist('_attachments/'.$filename) && preg_match('/^DR*/', $filename) && $this->hasVersion()) {

            return $this->getMother()->getAttachmentUri($filename);
        }

        return parent::getAttachmentUri($filename);
    }

    public function getCSV() {
        $csv = new DRCIVACsvFile($this->getAttachmentUri('DR.csv'));
        return $csv->getCsvAcheteur($this->identifiant);
    }

    public function updateFromCSV($updateProduitRevendique = false, $updatePrelevements = false,  $csv = null) {
    	if (!$this->hasDR() && !$csv) {
    		return;
    	}
        if(is_null($csv)) {
            $csv = $this->getCSV();
        }

        if($updatePrelevements) {
            $this->updatePrelevementsFromRevendication();
        }

        if($updateProduitRevendique) {
            $this->remove('declaration');
            $this->add('declaration');
        }

        $this->updateProduitDetailFromCSV($csv);

        if($updateProduitRevendique) {
            $this->updateProduitRevendiqueFromDetail();
        }

        $this->updateCepageFromCSV($csv);

        if($updatePrelevements) {
            $this->updatePrelevementsFromRevendication();
            $this->updateLotsFromCepage();
        }

        $this->declaration->reorderByConf();
    }

    public function updateFromDRev($drev) {
        foreach ($drev->getProduits() as $produit) {
            $this->addAppellation($produit->getAppellation()->getHash());
            if(!$produit->superficie_revendique && !$produit->volume_revendique) {
                continue;
            }
            $p = $this->addProduit($produit->getHash());
            if($this->isNonRecoltant()) {
                continue;
            }
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
        $produit->add('superficie_vinifiee');
        if($produit->getConfig()->hasProduitsVtsgn()) {
            $produit->add('volume_revendique_vtsgn');
            $produit->add('superficie_vinifiee_vtsgn');
            $produit->add('superficie_revendique_vtsgn');
            $produit->add('detail_vtsgn');
        }

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
                $this->chais->add($prelevement->getPrefix(), $chai->toArray(false, false));
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

    public function getPrelevementsByDate($filter_key = null, $force = false) {
        $prelevements = array();
        foreach ($this->prelevements as $prelevement) {
            if (!$prelevement->date && !$prelevement->total_lots && !$force) {

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

    public function getPrelevementsOrdered($filter_key = null, $force_date = false) {
        $drev_prelevements = $this->getPrelevementsByDate($filter_key, $force_date);
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
        $etapeOriginal = ($this->exist('etape')) ? $this->etape : null;

        $this->add('etape', $etape);

        return $etapeOriginal != $this->etape;
    }

    public function validate($date = null) {
        if(is_null($date)) {
            $date = date('Y-m-d');
        }

        $this->updatePrelevements();
        $this->cleanDoc();
        $this->validation = $date;
        $this->updateRegistreVCI();
    }

    public function devalidate() {
        $this->validation = null;
        $this->validation_odg = null;
        $this->remove('etape');
        $this->add('etape');

        $this->remove('mouvements');
        $this->add('mouvements');
    }

    public function updateRegistreVCI() {
    	$registreVCI = $this->getLastRegistreVCI();
    	if ($registreVCI && count($this->getProduitsVci()) > 0) {
    		$registreVCI = RegistreVCIClient::getInstance()->createDoc($this->identifiant, $this->campagne-1);
    	}
    	if ($registreVCI = $this->getLastRegistreVCI()) {
    		foreach ($this->getProduitsVci() as $produit) {
    			$hash = str_replace('/vci/', '/details/', $produit->getHash());
    			if (!preg_match('/\/cepage_/', $hash)) {
    				$hash = str_replace('/mention/lieu/couleur', '', $hash);
    			} else {
    				$hash = str_replace('/detail/0', '', $hash);
    			}
		   if (!$registreVCI->exist($hash)) {
			$hash = preg_replace('|/details/.+$|', '', $hash);
                    if($produit->destruction) {
                        $registreVCI->addLigne($hash, 'destruction', $produit->destruction, $produit->stockage_identifiant);
                    }
                    if($produit->complement) {
                        $registreVCI->addLigne($hash, 'complement', $produit->complement, $produit->stockage_identifiant);
                    }
                    if($produit->substitution) {
                        $registreVCI->addLigne($hash, 'substitution', $produit->substitution, $produit->stockage_identifiant);
                    }
                    if($produit->rafraichi) {
                        $registreVCI->addLigne($hash, 'rafraichi', $produit->rafraichi, $produit->stockage_identifiant);
                    }
                    continue;
    			}
    			$detail = $registreVCI->get($hash);
                if(round($produit->destruction - $detail->destruction, 2)) {
    			    $registreVCI->addLigne($detail->getParent()->getParent()->getHash(), 'destruction', round($produit->destruction - $detail->destruction, 2), $detail->stockage_identifiant);
                }
                if(round($produit->complement - $detail->complement, 2)) {
    			    $registreVCI->addLigne($detail->getParent()->getParent()->getHash(), 'complement', round($produit->complement -  $detail->complement, 2), $detail->stockage_identifiant);
                }
                if(round($produit->substitution - $detail->substitution, 2)) {
		            $registreVCI->addLigne($detail->getParent()->getParent()->getHash(), 'substitution', round($produit->substitution - $detail->substitution, 2), $detail->stockage_identifiant);
                }
                if(round($produit->rafraichi - $detail->rafraichi, 2)) {
			        $registreVCI->addLigne($detail->getParent()->getParent()->getHash(), 'rafraichi', round($produit->rafraichi - $detail->rafraichi, 2), $detail->stockage_identifiant);
                }
    		}
    		$registreVCI->save();
    	}
    }

    public function validateOdg($date = null) {
        if(is_null($date)) {
            $date = date('Y-m-d');
        }

        $this->validation_odg = $date;
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

    protected function updateProduitDetailFromCSV($csv) {
        $this->resetProduitDetail();
        foreach ($csv as $line) {
            $hashProduit = DRCIVACsvFile::getHashProduitByLine($line);

            if (!preg_match("/^TOTAL/", $line[DRCIVACsvFile::CSV_LIEU]) && !preg_match("/^TOTAL/", $line[DRCIVACsvFile::CSV_CEPAGE])) {

                continue;
            }

            $hashProduit = preg_replace("/(mentionVT|mentionSGN)/", "mention", $hashProduit);
            $hashProduit = preg_replace('|/recolte.|', '/declaration/', $hashProduit);

            if (!$this->getConfiguration()->exist($hashProduit)) {
                continue;
            }

            $config = $this->getConfiguration()->get($hashProduit)->getNodeRelation('revendication');

            if ($config instanceof ConfigurationCepage) {
                continue;
            }

            if ($config instanceof ConfigurationLieu) {
                continue;
            }

            if ($config->getLieu()->hasManyCouleur() && !$config instanceof ConfigurationCouleur) {
                continue;
            }

            if ($config instanceof ConfigurationAppellation) {
                $config = $config->mention->lieu->couleur;
            }

            if ($config instanceof ConfigurationMention) {
                $config = $config->lieu->couleur;
            }

            if (!$config instanceof ConfigurationCouleur) {
                continue;
            }

            $produit = $this->addProduit($config->getHash());
            $produitDetail = $produit->detail;
            if($line[DRCIVACsvFile::CSV_VTSGN]) {
                $produitDetail = $produit->detail_vtsgn;
            }

            $produitDetail->volume_total += (float) $line[DRCIVACsvFile::CSV_VOLUME_TOTAL];
            $produitDetail->usages_industriels_total += (float) $line[DRCIVACsvFile::CSV_USAGES_INDUSTRIELS_TOTAL];
            if(preg_match("/^[0-9\.,]+$/", $line[DRCIVACsvFile::CSV_VCI_TOTAL]) && ((float) $line[DRCIVACsvFile::CSV_VCI_TOTAL]) > 0) {
                $produitDetail->vci_total += (float) $line[DRCIVACsvFile::CSV_VCI_TOTAL];
            }
            $produitDetail->superficie_total += (float) $line[DRCIVACsvFile::CSV_SUPERFICIE_TOTALE];
            $produitDetail->volume_sur_place += (float) $line[DRCIVACsvFile::CSV_VOLUME];

            if (!$produitDetail->exist('superficie_vinifiee')) {
            	$produitDetail->add('superficie_vinifiee');
            }
            if($line[DRCIVACsvFile::CSV_SUPERFICIE] != "") {
                $produitDetail->superficie_vinifiee += (float) $line[DRCIVACsvFile::CSV_SUPERFICIE];
            } else {
                $produitDetail->superficie_vinifiee = null;
            }
            if ($line[DRCIVACsvFile::CSV_USAGES_INDUSTRIELS] == "") {
                $produitDetail->usages_industriels_sur_place = -1;
            } elseif ($produitDetail->usages_industriels_sur_place != -1) {
                $produitDetail->usages_industriels_sur_place += (float) $line[DRCIVACsvFile::CSV_USAGES_INDUSTRIELS];
            }

            if(preg_match("/^[0-9\.,]+$/", $line[DRCIVACsvFile::CSV_VCI]) && ((float) $line[DRCIVACsvFile::CSV_VCI]) > 0) {
                $produitDetail->vci_sur_place += (float) $line[DRCIVACsvFile::CSV_VCI];
            }
        }

        $this->updateProduitDetail();
    }

    protected function resetProduitDetail() {
        foreach ($this->declaration->getProduits() as $produit) {
            $produit->resetDetail();
        }
    }

    protected function updateProduitDetail() {
        foreach ($this->declaration->getProduits() as $produit) {
            $produit->updateDetail();
        }
    }

    protected function updateProduitRevendiqueFromDetail() {
        foreach ($this->declaration->getProduits() as $produit) {
            $produit->updateRevendiqueFromDetail();
        }
    }

    public function updatePrelevementsFromRevendication() {
        $prelevements_to_delete = array_flip($this->prelevement_keys);
        foreach ($this->declaration->getProduits() as $produit) {
            if (!$produit->getTotalVolumeRevendique()) {

                continue;
            }
            if (!$produit->getVolumeRevendiqueRecolte() && $this->isNonVinificateur()) {

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

        if ($this->declaration->hasVtsgn() && !$this->isNonVinificateur()) {
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
        $this->resetCepage();

        foreach ($csv as $line) {
            if (
                    preg_match("/^TOTAL/", $line[DRCIVACsvFile::CSV_APPELLATION]) ||
                    preg_match("/^TOTAL/", $line[DRCIVACsvFile::CSV_LIEU]) ||
                    preg_match("/^TOTAL/", $line[DRCIVACsvFile::CSV_CEPAGE])
            ) {

                continue;
            }

            $hashProduit = DRCIVACsvFile::getHashProduitByLine($line);

            $hash = preg_replace("|/detail/.+$|", "", preg_replace('|/recolte.|', '/declaration/', preg_replace("|/detail/[0-9]+$|", "", $hashProduit)));
            $hash = preg_replace("/(mentionVT|mentionSGN)/", "mention", $hash);

            if (!$this->getConfiguration()->exist($hash)) {
                continue;
            }

            $config = $this->getConfiguration()->get($hash);
            $detail = $this->getOrAdd($config->getHash())->addDetailNode($line[DRCIVACsvFile::CSV_LIEU]);
            if ($line[DRCIVACsvFile::CSV_VTSGN] == "VT") {
                $detail->volume_revendique_vt += (float) $line[DRCIVACsvFile::CSV_VOLUME];
                $detail->superficie_revendique_vt += (float) $line[DRCIVACsvFile::CSV_SUPERFICIE_TOTALE];
            } elseif ($line[DRCIVACsvFile::CSV_VTSGN] == "SGN") {
                $detail->volume_revendique_sgn += (float) $line[DRCIVACsvFile::CSV_VOLUME];
                $detail->superficie_revendique_sgn += (float) $line[DRCIVACsvFile::CSV_SUPERFICIE_TOTALE];
            } else {
                $detail->volume_revendique += (float) $line[DRCIVACsvFile::CSV_VOLUME];
                $detail->superficie_revendique += (float) $line[DRCIVACsvFile::CSV_SUPERFICIE_TOTALE];
            }

            $detail->updateTotal();
            $detail->getLibelle();
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
            if(!$produit->volume_revendique_total > 0) {
                continue;
            }


            $lot = $this->addLotProduit($produit->getCepage()->getHash(), self::CUVE);


            if (!$lot) {

                continue;
            }

            $prelevements[$lot->getPrelevement()->getKey()] = $lot->getPrelevement();
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

    public function updateProduitRevendiqueFromCepage() {
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

    public function isSauvegarde()
    {
    	$tabId = explode('-', $this->_id);
    	return (strlen($tabId[(count($tabId) - 1)]) > 4)? true : false;
    }

    public function canHaveSuperficieVinifiee()
    {
    	$can = false;
    	foreach ($this->declaration->getProduits() as $produit) {
    		if ($produit->exist('superficie_vinifiee') || $produit->exist('superficie_vinifiee_vtsgn')) {
    			$can = true;
    			break;
    		}
    	}
    	return $can;
    }

	protected function doSave() {
		$this->piece_document->generatePieces();
  }
  public function save($con = null) {
    if ($this->exist('non_conditionneur') && $this->non_conditionneur) {
      if ($this->prelevements->exist('cuve_ALSACE')) {
        $this->prelevements->cuve_ALSACE->add('force', 1);
      }
    }
    parent::save($con);
	}

    /*
     * Facture
     */
	public function getSurfaceFacturable()
	{
        $totalPrecedenteVersion = 0;

        if ($this->hasVersion()) {
            $totalPrecedenteVersion = $this->getMother()->declaration->getTotalTotalSuperficie();
        }

		return $this->declaration->getTotalTotalSuperficie();
	}

	public function getVolumeFacturable()
	{
        $totalPrecedenteVersion = 0;

        if ($this->hasVersion()) {
            $totalPrecedenteVersion = $this->getMother()->declaration->getTotalVolumeRevendique();
        }

		return $this->declaration->getTotalVolumeRevendique();
	}

	public function getSurfaceVinifieeFacturable()
	{
        $totalPrecedenteVersion = 0;

        if ($this->hasVersion()) {
            $totalPrecedenteVersion = $this->getMother()->declaration->getTotalSuperficieVinifiee();
        }

		return $this->declaration->getTotalSuperficieVinifiee();
	}

    public function isAdherentSyndicat() {

        return $this->getEtablissementObject()->getCompte()->isAdherentSyndicat();
    }

    public function getSyndicatsViticole() {

        return $this->getEtablissementObject()->getCompte()->getSyndicatsViticole();
    }

    /**** MOUVEMENTS ****/

    public function getTemplateFacture() {

        return TemplateFactureClient::getInstance()->find("TEMPLATE-FACTURE-AOC-".$this->getCampagne());
    }

    public function getMouvementsFactures() {

        return $this->_get('mouvements');
    }

    public function getMouvementsFacturesCalcule() {
        $templateFacture = $this->getTemplateFacture();

        if(!$templateFacture) {
            return array();
        }

        $cotisations = $templateFacture->generateCotisations($this);

        if($this->hasVersion()) {
            $cotisationsPrec = $templateFacture->generateCotisations($this->getMother());
        }

        $identifiantCompte = "E".$this->getIdentifiant();

        $mouvements = array();

        $rienAFacturer = true;

        foreach($cotisations as $cotisation) {
            $mouvement = DRevMouvementFactures::freeInstance($this);
            $mouvement->categorie = $cotisation->getCollectionKey();
            $mouvement->type_hash = $cotisation->getDetailKey();
            $mouvement->type_libelle = $cotisation->getLibelle();
            $mouvement->quantite = $cotisation->getQuantite();
            $mouvement->taux = $cotisation->getPrix();
            $mouvement->facture = 0;
            $mouvement->facturable = 1;
            $mouvement->date = $this->getCampagne().'-12-10';
            $mouvement->date_version = $this->validation;
            $mouvement->version = $this->version;
            $mouvement->template = $templateFacture->_id;

            if(isset($cotisationsPrec[$cotisation->getHash()])) {
                $mouvement->quantite = $mouvement->quantite - $cotisationsPrec[$cotisation->getHash()]->getQuantite();
            }

            if($this->hasVersion() && !$mouvement->quantite) {
                continue;
            }

            if($mouvement->quantite) {
                $rienAFacturer = false;
            }

            $mouvements[$mouvement->getMD5Key()] = $mouvement;
        }

        if($rienAFacturer) {
            return array($identifiantCompte => array());

        }

        return array($identifiantCompte => $mouvements);
    }

    public function getMouvementsFacturesCalculeByIdentifiant($identifiant) {

        return $this->mouvement_document->getMouvementsFacturesCalculeByIdentifiant($identifiant);
    }

    public function generateMouvementsFactures() {
        if(!$this->validation_odg) {

            return false;
        }

        if(!$this->getTemplateFacture()) {

            return false;
        }

        return $this->mouvement_document->generateMouvementsFactures();
    }

    public function findMouvementFactures($cle, $id = null){
      return $this->mouvement_document->findMouvementFactures($cle, $id);
    }

    public function facturerMouvements() {

        return $this->mouvement_document->facturerMouvements();
    }

    public function isFactures() {

        return $this->mouvement_document->isFactures();
    }

    public function isNonFactures() {

        return $this->mouvement_document->isNonFactures();
    }

    public function clearMouvementsFactures(){
        $this->remove('mouvements');
        $this->add('mouvements');
    }

    /**** FIN DES MOUVEMENTS ****/

    /**** PIECES ****/

    public function getAllPieces() {
    	$complement = ($this->isPapier())? '(Papier)' : '(Télédéclaration)';
    	$complement .= ($this->isSauvegarde())? ' Non facturé' : '';
        $version = ($this->hasVersion()) ? ' Version '.(str_replace("M", "", $this->version)*1) : "";

        return (!$this->getValidation())? array() : array(array(
    		'identifiant' => $this->getIdentifiant(),
    		'date_depot' => $this->getValidation(),
            'libelle' => 'Revendication des appellations viticoles '.$this->campagne.$version.' '.$complement,
    		'mime' => Piece::MIME_PDF,
    		'visibilite' => 1,
    		'source' => null
    	));
    }

    public function generatePieces() {
    	return $this->piece_document->generatePieces();
    }

    public function generateUrlPiece($source = null) {
    	return sfContext::getInstance()->getRouting()->generate('drev_export_pdf', $this);
    }

    public static function getUrlVisualisationPiece($id, $admin = false) {
    	return sfContext::getInstance()->getRouting()->generate('drev_visualisation', array('id' => $id));
    }

    public static function getUrlGenerationCsvPiece($id, $admin = false) {
    	return null;
    }

    public static function isVisualisationMasterUrl($admin = false) {
    	return true;
    }

    public static function isPieceEditable($admin = false) {
    	return false;
    }

    /**** FIN DES PIECES ****/

    /**** VERSION ****/

    public static function buildVersion($rectificative, $modificative) {

        return VersionDocument::buildVersion($rectificative, $modificative);
    }

    public static function buildRectificative($version) {

        return VersionDocument::buildRectificative($version);
    }

    public static function buildModificative($version) {

        return VersionDocument::buildModificative($version);
    }

    public function getVersion() {

        return $this->_get('version');
    }

    public function hasVersion() {

        return $this->version_document->hasVersion();
    }

    public function isVersionnable() {
        if (!$this->validation) {

            return false;
        }

        return $this->version_document->isVersionnable();
    }

    public function getRectificative() {

        return $this->version_document->getRectificative();
    }

    public function isRectificative() {

        return $this->version_document->isRectificative();
    }

    public function isRectifiable() {

        return false;
    }

    public function getModificative() {

        return $this->version_document->getModificative();
    }

    public function isModificative() {

        return $this->version_document->isModificative();
    }

    public function isModifiable() {
        return $this->version_document->isModifiable();
    }

    public function isTeledeclareFacturee() {
        return $this->isTeledeclare() && !$this->isNonFactures();
    }

    public function isTeledeclareNonFacturee() {
        return $this->isTeledeclare() && $this->isNonFactures();
    }

    public function getPreviousVersion() {

        return $this->version_document->getPreviousVersion();
    }

    public function getMasterVersionOfRectificative() {

        throw new sfException("Not implemented");
    }

    public function needNextVersion() {

        return $this->version_document->needNextVersion() || !$this->isSuivanteCoherente();
    }

    public function getMaster() {

        return $this->version_document->getMaster();
    }

    public function isMaster() {

        return $this->version_document->isMaster();
    }

    public function findMaster() {

        return DRevClient::getInstance()->findMasterByIdentifiantAndCampagne($this->identifiant, $this->campagne);
    }

    public function findDocumentByVersion($version) {
        $id = 'DREV-' . $this->identifiant . '-' . $this->campagne;
        if($version) {
            $id .= "-".$version;
        }

        return DRevClient::getInstance()->find($id);
    }

    public function getMother() {

        return $this->version_document->getMother();
    }

    public function motherGet($hash) {

        return $this->version_document->motherGet($hash);
    }

    public function motherExist($hash) {

        return $this->version_document->motherExist($hash);
    }

    public function motherHasChanged() {
        if ($this->declaration->total != $this->getMother()->declaration->total) {

            return true;
        }

        if (count($this->getProduitsDetails($this->teledeclare)) != count($this->getMother()->getProduitsDetails($this->teledeclare))) {

            return true;
        }

        if ($this->droits->douane->getCumul() != $this->getMother()->droits->douane->getCumul()) {

            return true;
        }

        return false;
    }

    public function getDiffWithMother() {

        return $this->version_document->getDiffWithMother();
    }

    public function isModifiedMother($hash_or_object, $key = null) {

        return $this->version_document->isModifiedMother($hash_or_object, $key);
    }

    public function generateRectificative() {

        return $this->version_document->generateRectificative();
    }

    public function generateModificative() {
        $drm_modificatrice = $this->version_document->generateModificative();

        return $drm_modificatrice;
    }

    public function generateNextVersion() {

        throw new sfException("Not implemented");
    }

    public function listenerGenerateVersion($document) {
        $document->devalidate();
    }

    public function listenerGenerateNextVersion($document) {

    }

    public function getSuivante() {

        throw new sfException("Not implemented");
    }

    public function getPeriode() {
        $periode = $this->_get('campagne');
        if (strpos($periode, '-') !== false) {
            $periode = substr($periode, 0, 4);
        }
        return $periode;
    }

    public function getDate() {
        return $this->getPeriode().'-12-15';
    }

    /**** FIN DE VERSION ****/
}
