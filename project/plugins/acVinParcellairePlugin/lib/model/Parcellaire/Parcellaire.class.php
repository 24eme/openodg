<?php

/**
 * Model for Parcellaire
 *
 */
class Parcellaire extends BaseParcellaire {

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

    public function storeDeclarant() {
        $this->declarant_document->storeDeclarant();
    }

    public function getEtablissementObject() {

        return EtablissementClient::getInstance()->findByIdentifiant($this->identifiant);
    }

    public function initDoc($identifiant, $campagne) {
        $this->identifiant = $identifiant;
        $this->campagne = $campagne;
        $this->set('_id', ParcellaireClient::getInstance()->buildId($this->identifiant, $this->campagne));
        $this->storeDeclarant();
    }

    public function getConfiguration() {
        return acCouchdbManager::getClient('Configuration')->retrieveConfiguration($this->campagne);
    }

    public function storeEtape($etape) {
        $this->add('etape', $etape);
    }

    public function isPapier() {
        return $this->exist('papier') && $this->get('papier');
    }

    public function hasVendeurs() {
        return count($this->vendeurs);
    }

    public function initProduitFromLastParcellaire() {
        if (count($this->declaration) == 0) {
            $this->importProduitsFromLastParcellaire();
        }
    }

    private function importProduitsFromLastParcellaire() {
        $campagnePrec = $this->campagne - 1;
        $parcellairePrevId = ParcellaireClient::getInstance()->buildId($this->identifiant, $campagnePrec);
        $parcellairePrev = ParcellaireClient::getInstance()->find($parcellairePrevId);
        if (!$parcellairePrev) {
            return;
        }
        $this->declaration = $parcellairePrev->declaration;
    }

    public function getProduits($onlyActive = false) {
        return $this->declaration->getProduits($onlyActive = false);
    }

    public function getAllParcellesByAppellations() {
        $appellations = $this->declaration->getAppellations();
        $parcellesByAppellations = array();
        foreach ($appellations as $appellation) {
            $parcellesByAppellations[$appellation->getHash()] = new stdClass();
            $parcellesByAppellations[$appellation->getHash()]->appellation = $appellation;
            $parcellesByAppellations[$appellation->getHash()]->parcelles = $appellation->getProduitsCepageDetails();
        }
        return $parcellesByAppellations;
    }

    public function updateParcellesForAppellation($appellationKey, $produits) {
        $appellations = $this->declaration->getAppellations();
        $appellationNode = null;
        $appellationNodeHash = null;
        foreach ($appellations as $key => $appellation) {
            if ('appellation_' . $appellationKey == $key) {
                $appellationNode = $appellation;
                $appellationNodeHash = $appellation->getHash();
                break;
            }
        }

        if ($appellationNode) {
            $this->remove($appellationNodeHash);
            $this->getOrAdd($appellationNodeHash);
            foreach ($produits as $cepageKey => $parcelle) {
                $cepageKeyMatches = array();
                preg_match('/^(.*)-detail-(.*)$/', $cepageKey, $cepageKeyMatches);
                if (count($cepageKeyMatches) != 3) {
                    throw new sfException("La hash produit " . $cepageKey . " n'est pas conforme");
                }
                $hashCepage = str_replace('-', '/', $parcelle["cepage"]);
                $parcelleKey = $cepageKeyMatches[2];
                $this->addProduitParcelle($hashCepage, $parcelleKey, $parcelle["commune"], $parcelle["section"], $parcelle["numero_parcelle"], $parcelle["superficie"]);
            }
        }
        //$nouveauNoeudAppellation->getParent()->reorderByConf();
    }

    public function addProduit($hash, $add_appellation = true) {
        $config = $this->getConfiguration()->get($hash);
        if ($add_appellation) {
            $this->addAppellation($config->getAppellation()->getHash());
        }
        $produit = $this->getOrAdd($config->getHash());
        $produit->getLibelle();

        return $produit;
    }

    public function addProduitParcelle($hash, $parcelleKey, $commune, $section, $numero_parcelle, $superficie) {
        $produit = $this->getOrAdd($hash);

        $this->addProduit($produit->getProduitHash());

        return $produit->addDetailNode($parcelleKey, $commune, $section, $numero_parcelle, $superficie);
    }

    public function addAppellation($hash) {
        $config = $this->getConfiguration()->get($hash);
        $appellation = $this->getOrAdd($config->hash);
//        $config_produits = $appellation->getConfigProduits();
//        if (count($config_produits) == 1) {
//            reset($config_produits);
//            $this->addProduitCepage(key($config_produits), null, false);
//        } else {
//            foreach ($config_produits as $hash => $config_produit) {
//                if ($config_produit->isAutoDRev()) {
//                    $this->addProduitCepage($hash, null, false);
//                }
//            }
//        }

        return $appellation;
    }

    public function getParcellesByCommunes() {
        $parcellesByCommunes = array();
        $allParcellesByAppellations = $this->getAllParcellesByAppellations();
        $config = $this->getConfiguration();
        foreach ($allParcellesByAppellations as $appellation_key => $parcellesNodes) {
            $configAppellationLibelle = $config->get($appellation_key)->getLibelle();
            foreach ($parcellesNodes->parcelles as $key => $parcelle) {
                if (!array_key_exists($parcelle->commune, $parcellesByCommunes)) {
                    $parcellesByCommunes[$parcelle->commune] = new stdClass();
                    $parcellesByCommunes[$parcelle->commune]->commune = $parcelle->commune;
                    $parcellesByCommunes[$parcelle->commune]->total_superficie = 0;
                    $parcellesByCommunes[$parcelle->commune]->produits = array();
                }
                $key_produit = $parcelle->commune . '-' . $parcelle->section . '-' . $parcelle->numero_parcelle;
                $parcellesByCommunes[$parcelle->commune]->produits[$key_produit] = new stdClass();
                
                $configLieuLibelle = $config->get($parcelle->getCepage()->getCouleur()->getLieu()->getHash())->getLibelle();
                $configCepageLibelle = $config->get($parcelle->getCepage()->getHash())->getLibelle();
                
                $parcellesByCommunes[$parcelle->commune]->produits[$key_produit]->appellation_libelle = $configAppellationLibelle;
                $parcellesByCommunes[$parcelle->commune]->produits[$key_produit]->lieu_libelle = $configLieuLibelle;
                $parcellesByCommunes[$parcelle->commune]->produits[$key_produit]->cepage_libelle = $configCepageLibelle;
                $parcellesByCommunes[$parcelle->commune]->produits[$key_produit]->num_parcelle = $parcelle->section . ' ' . $parcelle->numero_parcelle;
                $parcellesByCommunes[$parcelle->commune]->produits[$key_produit]->superficie = $parcelle->superficie;
                $parcellesByCommunes[$parcelle->commune]->total_superficie += $parcelle->superficie;
            }
        }
        return $parcellesByCommunes;
    }

    public function getFirstAppellation() {
        return 'LIEUDIT';
    }

}
