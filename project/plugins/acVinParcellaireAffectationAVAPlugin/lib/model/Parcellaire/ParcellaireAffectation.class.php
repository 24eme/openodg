<?php

/**
 * Model for Parcellaire
 *
 */
class ParcellaireAffectation extends BaseParcellaireAffectation implements InterfaceDeclaration, InterfacePieceDocument {

    protected $declarant_document = null;
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
        $this->piece_document = new PieceDocument($this);
    }

    public function storeDeclarant() {
        $this->declarant_document->storeDeclarant();
    }

    public function getEtablissementObject() {

        return EtablissementClient::getInstance()->findByIdentifiant($this->identifiant);
    }

    public function initDoc($identifiant, $campagne, $type = ParcellaireAffectationClient::TYPE_COUCHDB) {
        $this->identifiant = $identifiant;
        $this->campagne = $campagne;
        $this->set('_id', ParcellaireAffectationClient::getInstance()->buildId($this->identifiant, $this->campagne, $type));
        $this->storeDeclarant();
    }

    public function getAcheteursByCVI() {
        $acheteursCvi = array();
        foreach ($this->acheteurs as $type => $acheteurs) {
            foreach ($acheteurs as $cvi => $acheteur) {
                $acheteursCvi[$cvi] = $acheteur;
            }
        }

        return $acheteursCvi;
    }

    public function getAcheteursByHash() {
        $acheteurs = array();

        foreach ($this->getDocument()->acheteurs as $achs) {
            foreach ($achs as $acheteur) {
                $acheteurs[$acheteur->getHash()] = sprintf("%s", $acheteur->nom);
            }
        }

        return $acheteurs;
    }

    public function getConfiguration() {
        return acCouchdbManager::getClient('Configuration')->retrieveConfiguration($this->campagne);
    }

    public function storeEtape($etape) {
        if ($etape == $this->etape) {

            return false;
        }

        $this->add('etape', $etape);

        return true;
    }

    public function isPapier() {

        return $this->exist('papier') && $this->get('papier');
    }

    public function isLectureSeule() {

        return $this->exist('lecture_seule') && $this->get('lecture_seule');
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

    public function hasVendeurs() {
        return count($this->vendeurs);
    }

    public function initProduitFromLastParcellaire() {
        if (count($this->declaration) == 0) {
            $this->importProduitsFromLastParcellaire();
        }
    }

    public function updateAffectationCremantFromCVI() {
        $cepages_autorises = [
            'cepage_PB' => 'PINOT BLANC',
            'cepage_CD' => 'CHARDONNAY',
            'cepage_BN' => 'PINOT NOIR BLANC',
            'cepage_RI' => 'RIESLING',
            'cepage_PG' => 'PINOT GRIS',
            'cepage_PN' => 'PINOT NOIR ROSé',
            'cepage_BLRS' => 'BLANC + ROSé',
            'cepage_RB' => 'REBêCHES',
            'cepage_PNRaisin' => 'PINOT NOIR',
            'cepage_AU' => 'AUXERROIS'
        ];

        $parcellesFromLastAffectation = $this->getAllParcellesByAppellation("CREMANT");
        $parcellesFromCurrentAffectation = [];

        foreach (ParcellaireClient::getInstance()->getLast($this->identifiant)->declaration as $CVIAppellation) {
            foreach ($CVIAppellation->detail as $CVIParcelle) {
                $c = false;
                foreach ($cepages_autorises as $k => $cep) {
                    if (strpos(strtolower($CVIParcelle->getCepage()), strtolower($cep)) !== false) {
                        $c = $k;
                        break;
                    }
                }

                if ($c) {
                    $hash = "/declaration/certification/genre/appellation_CREMANT/mention/lieu/couleur/$c";
                    $parcellesFromCurrentAffectation[$hash.'/detail/'.$CVIParcelle->getKey()] = $this->addProduitParcelle($hash, $CVIParcelle->getKey(), $CVIParcelle->getCommune(), $CVIParcelle->getSection(), $CVIParcelle->getNumeroParcelle(), $CVIParcelle->getLieu());
                    $parcellesFromCurrentAffectation[$hash.'/detail/'.$CVIParcelle->getKey()]->superficie = $CVIParcelle->superficie;
                    $parcellesFromCurrentAffectation[$hash.'/detail/'.$CVIParcelle->getKey()]->active = 0;
                }
            }
        }

        // On vire les parcelles qui ne sont pas dans le parcellaire
        foreach (array_diff(array_keys($parcellesFromLastAffectation), array_keys($parcellesFromCurrentAffectation)) as $parcellesASuppr) {
            $this->remove($parcellesASuppr);
        }

        // On active les anciennes parcelles automatiquement
        foreach (array_intersect(array_keys($parcellesFromLastAffectation), array_keys($parcellesFromCurrentAffectation)) as $parcellesAActiver) {
            $this->get($parcellesAActiver)->active = 1;
        }
    }

    public function updateAffectationCremantFromLastTwoIntentions()
    {
        $intention = $this->getParcellaireLastCampagne("INTENTIONCREMANT");

        if (! $intention) {
            return;
        }

        $cepages_autorises = [
            'cepage_PB' => 'PINOT BLANC',
            'cepage_CD' => 'CHARDONNAY',
            'cepage_BN' => 'PINOT NOIR BLANC',
            'cepage_RI' => 'RIESLING',
            'cepage_PG' => 'PINOT GRIS',
            'cepage_PN' => 'PINOT NOIR ROSé',
            'cepage_BLRS' => 'BLANC + ROSé',
            'cepage_RB' => 'REBêCHES',
            'cepage_PNRaisin' => 'PINOT NOIR',
            'cepage_AU' => 'AUXERROIS'
        ];

        foreach ($intention->getAllParcellesByAppellation(ParcellaireAffectationClient::APPELLATION_CREMANT) as $parcelleIntention) {
            if (array_key_exists($parcelleIntention->getCepage()->getKey(), $cepages_autorises)) {
                $hash = "/declaration/certification/genre/appellation_CREMANT/mention/lieu/couleur/".$parcelleIntention->getCepage()->getKey()."/detail/".$parcelleIntention->getKey();

                if ($this->exist($hash)) {
                    $this->get($hash)->superficie = $parcelleIntention->superficie;
                    $this->get($hash)->active = 1;
                }
            }
        }
    }

    public function getParcellaireLastCampagne($type = null) {
        if ($type === null) {
            $type = $this->getTypeParcellaire();
        }

        $campagnePrec = $this->campagne - 1;
        $parcellairePrevId = ParcellaireAffectationClient::getInstance()->buildId($this->identifiant, $campagnePrec, $type);
        $parcellaire = ParcellaireAffectationClient::getInstance()->find($parcellairePrevId);

        if (!$parcellaire) {
            $campagnePrec = $this->campagne - 2;
            $parcellairePrevId = ParcellaireAffectationClient::getInstance()->buildId($this->identifiant, $campagnePrec, $type);
            $parcellaire = ParcellaireAffectationClient::getInstance()->find($parcellairePrevId);
        }

        return $parcellaire;
    }

    private function importProduitsFromLastParcellaire() {
        $parcellairePrev = $this->getParcellaireLastCampagne();
        if (!$parcellairePrev) {
            return;
        }

        $this->declaration = $parcellairePrev->declaration;
    }

    public function fixSuperficiesHa() {
        foreach ($this->declaration->getProduitsCepageDetails() as $detail) {
            if (preg_match("/^[0-9]+\.[0-9]{3,}$/", $detail->superficie) || ($detail->superficie < 2 && $detail->getAppellation()->getKey() == "appellation_GRDCRU")) {
                $old_superficie = $detail->superficie;
                $detail->superficie = $detail->superficie * 100;
                echo "REWRITE SUPERFICIE;" . $this->_id . ";" . $detail->getLibelleComplet() . ";" . $old_superficie . ";" . $detail->superficie . "\n";
            }
        }
    }

    public function getProduits($onlyActive = false) {
        return $this->declaration->getProduits($onlyActive = false);
    }

    public function getAllParcellesKeysByAppellations() {
        $appellations = $this->declaration->getAppellations();
        $parcellesByAppellations = array();
        foreach ($appellations as $appellation) {
            $parcellesByAppellations[$appellation->getHash()] = array();
            foreach ($appellation->getProduitsCepageDetails() as $detail) {
                $parcellesByAppellations[$appellation->getHash()][$detail->getHash()] = $detail;
            }
        }
        return $parcellesByAppellations;
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

    public function getAllParcellesByAppellation($appellationHash) {
        $allParcellesByAppellations = $this->getAllParcellesByAppellations();
        $parcelles = array();

        foreach ($allParcellesByAppellations as $appellation) {
            $appellationKey = str_replace('appellation_', '', $appellation->appellation->getKey());
            if ($appellationKey == $appellationHash) {
                $parcelles = $appellation->parcelles;
            }
        }
        return $parcelles;
    }

    public function getAppellationNodeFromAppellationKey($appellationKey, $autoAddAppellation = false) {
        if ($appellationKey == ParcellaireAffectationClient::APPELLATION_VTSGN) {
            return ParcellaireAffectationClient::APPELLATION_VTSGN;
        }

        $appellations = $this->declaration->getAppellations();
        $appellationNode = null;
        foreach ($appellations as $key => $appellation) {
            if ('appellation_' . $appellationKey == $key) {
                $appellationNode = $appellation;
                break;
            }
        }
        if (!$appellationNode && $autoAddAppellation) {
            foreach ($this->getConfiguration()->getDeclaration()->getNoeudAppellations() as $key => $appellation) {
                if ('appellation_' . $appellationKey == $key) {
                    $appellationNode = $this->addAppellation($appellation->getHash());
                    break;
                }
            }
        }
        return $appellationNode;
    }

    public function addProduit($hash, $add_appellation = true) {
        $config = $this->getConfiguration()->get($hash);
        if ($add_appellation) {
            $this->addAppellation($config->getAppellation()->getHash());
        }

        $produit = $this->getOrAdd($config->getHash());
        $produit->getLieu()->getLibelle();
        $produit->getCouleur()->getLibelle();
        $produit->getLibelle();

        return $produit;
    }

    public function addProduitParcelle($hash, $parcelleKey, $commune, $section, $numero_parcelle, $lieu = null, $dpt = null) {
        $produit = $this->getOrAdd($hash);
        $this->addProduit($produit->getHash());

        return $produit->addDetailNode($parcelleKey, $commune, $section, $numero_parcelle, $lieu, $dpt);
    }

    public function addParcelleForAppellation($appellationKey, $cepage, $commune, $section, $numero_parcelle, $lieu = null, $dpt = null) {
        $hash = str_replace('-', '/', $cepage);
        $commune = KeyInflector::slugify($commune);
        $section = KeyInflector::slugify($section);
        $numero_parcelle = KeyInflector::slugify($numero_parcelle);
        $parcelleKey = KeyInflector::slugify($commune . '-' . $section . '-' . $numero_parcelle);
        if ($lieu) {
            $parcelleKey.='-' . KeyInflector::slugify($lieu);
        }

        return $this->addProduitParcelle($hash, $parcelleKey, $commune, $section, $numero_parcelle, $lieu, $dpt);
    }

    public function addAppellation($hash) {
        $config = $this->getConfiguration()->get($hash);
        $appellation = $this->getOrAdd($config->hash);

        return $appellation;
    }

    public function updateAcheteursInfos() {
        foreach($this->acheteurs as $type => $acheteurs) {
            foreach($acheteurs as $cvi => $acheteur) {
                if ($cvi == $this->identifiant) {
                    continue;
                }
                $etablissement = EtablissementClient::getInstance()->find('ETABLISSEMENT-' . $cvi, acCouchdbClient::HYDRATE_JSON);

                if (!$etablissement) {
                    throw new sfException(sprintf("L'acheteur %s n'a pas été trouvé", 'ETABLISSEMENT-' . $cvi));
                }

                $change = false;
                if($acheteur->nom != $etablissement->raison_sociale || $acheteur->commune != $etablissement->commune || $acheteur->email != $etablissement->email) {
                    $change = true;
                }

                $acheteur->nom = $etablissement->raison_sociale;
                $acheteur->commune = $etablissement->commune;
                $acheteur->email = $etablissement->email;

                if($change && $acheteur->email_envoye)  {
                    $acheteur->email_envoye = null;
                }


            }
        }

        foreach($this->getProduits() as $produit) {
            foreach($produit->acheteurs as $lieu => $lieux) {
                foreach($lieux as $type => $types) {
                    foreach($types as $cvi => $acheteur) {
                        if ($cvi == $this->identifiant) {
                            continue;
                        }
                        $a = $this->acheteurs->get($type)->get($cvi);
                        $acheteur->nom = $a->nom;
                        $acheteur->cvi = $a->cvi;
                        $acheteur->commune = $a->commune;
                    }
                }
            }
        }
    }

    public function addAcheteur($type, $cvi) {
        if ($this->acheteurs->add($type)->exist($cvi)) {

            return $this->acheteurs->add($type)->get($cvi);
        }

        $acheteur = $this->acheteurs->add($type)->add($cvi);

        if ($cvi == $this->identifiant) {
            $acheteur->nom = "Sur place";
            $acheteur->cvi = $cvi;
            $acheteur->commune = null;

            return $acheteur;
        }

        $etablissement = EtablissementClient::getInstance()->find('ETABLISSEMENT-' . $cvi, acCouchdbClient::HYDRATE_JSON);

        if (!$etablissement) {
            throw new sfException(sprintf("L'acheteur %s n'a pas été trouvé", 'ETABLISSEMENT-' . $cvi));
        }

        $acheteur->nom = $etablissement->raison_sociale;
        $acheteur->cvi = $cvi;
        $acheteur->commune = $etablissement->commune;
        $acheteur->email = $etablissement->email;

        return $acheteur;
    }

    public function hasParcelleForAppellationKey($appellationKey) {
        $allParcelles = $this->getAllParcellesByAppellations();
        foreach ($allParcelles as $hash => $appellation) {
            if ($appellation->appellation->getKey() == 'appellation_' . $appellationKey) {
                foreach ($appellation->appellation->getMentions() as $mention) {
                    if (!count($mention->getLieux())) {
                        return false;
                    }
                }
                return true;
            }
        }
        return false;
    }

    public function getParcellesByAppellation($cviFilter = null) {
        $parcellesByAppellations = array();
        $appellationsPos = array_flip(array_keys(ParcellaireAffectationClient::getInstance()->getAppellationsKeys($this->getTypeParcellaire())));
        foreach ($this->declaration->getProduitsCepageDetails() as $parcelle) {
            $acheteurs = $parcelle->getAcheteursByCVI();
            if($cviFilter) {
                if(!array_key_exists($cviFilter, $acheteurs)) {
                    continue;
                }
            }
            $keyApp = sprintf("%s. %s", $appellationsPos[str_replace("appellation_", "", $parcelle->getLieuNode()->getAppellation()->getKey())], $parcelle->getLieuNode()->getAppellation()->getLibelle());
            if (!array_key_exists($keyApp, $parcellesByAppellations)) {
                $parcellesByAppellations[$keyApp] = new stdClass();
                $parcellesByAppellations[$keyApp]->total_superficie = 0;
                $parcellesByAppellations[$keyApp]->appellation_libelle = $parcelle->getAppellation()->getLibelle();
                $parcellesByAppellations[$keyApp]->lieu_libelle = '';
                $parcellesByAppellations[$keyApp]->parcelles = array();
                $parcellesByAppellations[$keyApp]->acheteurs = array();
            }

            $parcellesByAppellations[$keyApp]->acheteurs = $parcellesByAppellations[$keyApp]->acheteurs + $acheteurs;

            $parcellesByAppellations[$keyApp]->parcelles[$parcelle->gethash()] = new stdClass();
            $parcellesByAppellations[$keyApp]->parcelles[$parcelle->gethash()]->cepage_libelle = ($parcelle->getLieuLibelle()) ? $parcelle->getLieuLibelle().' - ' : '';
            $parcellesByAppellations[$keyApp]->parcelles[$parcelle->gethash()]->cepage_libelle .= $parcelle->getCepageLibelle();
            $parcellesByAppellations[$keyApp]->parcelles[$parcelle->gethash()]->parcelle = $parcelle;
            $parcellesByAppellations[$keyApp]->total_superficie += $parcelle->superficie;
        }

        ksort($parcellesByAppellations);

        return $parcellesByAppellations;
    }

    public function getParcellesByLieux($cviFilter = null) {
        $parcellesByLieux = array();
        $appellationsPos = array_flip(array_keys(ParcellaireAffectationClient::getInstance()->getAppellationsKeys($this->getTypeParcellaire())));
        foreach ($this->declaration->getProduitsCepageDetails() as $parcelle) {
            $acheteurs = $parcelle->getAcheteursByCVI();
            if($cviFilter) {
                if(!array_key_exists($cviFilter, $acheteurs)) {
                    continue;
                }
            }
            $keyLieu = sprintf("%s. %s %s", $appellationsPos[str_replace("appellation_", "", $parcelle->getLieuNode()->getAppellation()->getKey())], $parcelle->getLieuNode()->getAppellation()->getLibelle(), $parcelle->getLieuLibelle());
            if (!array_key_exists($keyLieu, $parcellesByLieux)) {
                $parcellesByLieux[$keyLieu] = new stdClass();
                $parcellesByLieux[$keyLieu]->total_superficie = 0;
                $parcellesByLieux[$keyLieu]->appellation_libelle = $parcelle->getAppellation()->getLibelle();
                $parcellesByLieux[$keyLieu]->lieu_libelle = $parcelle->getLieuLibelle();
                $parcellesByLieux[$keyLieu]->parcelles = array();
                $parcellesByLieux[$keyLieu]->acheteurs = array();
            }
            $parcellesByLieux[$keyLieu]->acheteurs = $parcellesByLieux[$keyLieu]->acheteurs + $acheteurs;

            $parcellesByLieux[$keyLieu]->parcelles[$parcelle->gethash()] = new stdClass();
            $parcellesByLieux[$keyLieu]->parcelles[$parcelle->gethash()]->cepage_libelle = $parcelle->getCepageLibelle();
            $parcellesByLieux[$keyLieu]->parcelles[$parcelle->gethash()]->parcelle = $parcelle;
            $parcellesByLieux[$keyLieu]->total_superficie += $parcelle->superficie;
        }

        ksort($parcellesByLieux);

        return $parcellesByLieux;
    }

    public function getParcellesByLieuxCommuneAndCepage($cviFilter = null) {
        $parcellesByLieuxCommuneAndCepage = array();

        foreach ($this->getParcellesByLieux($cviFilter) as $parcellesByLieu) {
            foreach ($parcellesByLieu->parcelles as $detailHash => $parcelle) {
                $key = $parcelle->parcelle->getCepage()->getHash() . '/' . $parcelle->parcelle->commune;
                if ($parcelle->parcelle->lieu) {
                    $key.='/' . $parcelle->parcelle->lieu;
                }
                if (!array_key_exists($key, $parcellesByLieuxCommuneAndCepage)) {
                    $parcellesByLieuxCommuneAndCepage[$key] = new stdClass();
                    $parcellesByLieuxCommuneAndCepage[$key]->total_superficie = 0;
                }
                $parcellesByLieuxCommuneAndCepage[$key]->total_superficie += $parcelle->parcelle->superficie;
                $parcellesByLieuxCommuneAndCepage[$key]->cepage_libelle = $parcelle->parcelle->cepage_libelle;
                $parcellesByLieuxCommuneAndCepage[$key]->commune = $parcelle->parcelle->commune;
                if (!$parcellesByLieu->lieu_libelle) {
                    $parcellesByLieuxCommuneAndCepage[$key]->appellation_lieu_libelle = $parcellesByLieu->appellation_libelle . ' VTSGN';
                } else {
                    $parcellesByLieuxCommuneAndCepage[$key]->appellation_lieu_libelle = $parcellesByLieu->appellation_libelle;
                    if (!$this->isParcellaireCremant()) {
                        $parcellesByLieuxCommuneAndCepage[$key]->appellation_lieu_libelle.=' - ' . $parcellesByLieu->lieu_libelle;
                    }
                }
            }
        }

        return $parcellesByLieuxCommuneAndCepage;
    }

    public function hasProduitWithMultipleAcheteur() {
        foreach($this->getProduits() as $produit) {
            if($produit->hasMultipleAcheteur()) {

                return true;
            }
        }

        return false;
    }

    public function hasAcheteursExternes()
    {
        foreach ($this->acheteurs as $type => $acheteurs) {
            if (in_array($type, array_keys(ParcellaireAffectationClient::$destinations_libelles)) && $type !== ParcellaireAffectationClient::DESTINATION_SUR_PLACE) {
                return true;
            }
        }

        return false;
    }

    public function validate($date = null) {
        if (is_null($date)) {
            $date = date('Y-m-d');
        }

        $this->declaration->cleanNode();
        $this->validation = $date;
        $this->validateOdg();
    }

    public function devalidate() {
        $this->validation = null;
        $this->validation_odg = null;
        $this->etape = null;
        foreach($this->getAcheteursByCVI() as $acheteur) {
            $acheteur->email_envoye = null;
        }
    }

    public function hasVtsgn() {

        return $this->declaration->hasVtsgn();
    }

    public function validateOdg() {
        $this->validation_odg = date('Y-m-d');
    }

    public function isParcellaireCremant() {
        return in_array($this->getTypeParcellaire(), array(ParcellaireAffectationClient::TYPE_COUCHDB_PARCELLAIRE_CREMANT, ParcellaireAffectationClient::TYPE_COUCHDB_INTENTION_CREMANT));
    }

    public function isIntentionCremant() {
    	return ($this->getTypeParcellaire() == ParcellaireAffectationClient::TYPE_COUCHDB_INTENTION_CREMANT);
    }

    public function getTypeParcellaire() {
    	if ($this->_id) {
    		if (preg_match('/^([A-Z]*)-([0-9]*)-([0-9]{4})/', $this->_id, $result)) {
    			return $result[1];
    		}
    	}
    	throw new sfException("Impossible de determiner le type de parcellaire");
    }

    protected function doSave() {
    	$this->piece_document->generatePieces();
    }

    /**** PIECES ****/

    public function getAllPieces() {
    	$complement = ($this->isPapier())? '(Papier)' : '(Télédéclaration)';
    	$cremant = ($this->isParcellaireCremant())? 'Crémant ' : '';
    	$title = ($this->isIntentionCremant())? 'Intention de production' : 'Affectation parcellaire';
    	return (!$this->getValidation())? array() : array(array(
    		'identifiant' => $this->getIdentifiant(),
    		'date_depot' => $this->getValidation(),
    		'libelle' => $title.' '.$cremant.$this->campagne.' '.$complement,
    		'mime' => Piece::MIME_PDF,
    		'visibilite' => 1,
    		'source' => null
    	));
    }

    public function generatePieces() {
    	return $this->piece_document->generatePieces();
    }

    public function generateUrlPiece($source = null) {
    	return sfContext::getInstance()->getRouting()->generate('parcellaire_export_pdf', $this);
    }

    public static function getUrlVisualisationPiece($id, $admin = false) {
    	return sfContext::getInstance()->getRouting()->generate('parcellaire_visualisation', array('id' => $id));
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

}
