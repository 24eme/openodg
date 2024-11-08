<?php

abstract class DouaneProduction extends Fichier implements InterfaceMouvementFacturesDocument, InterfaceDeclarantDocument {

    const FAMILLE_COOPERATIVE = 'COOPERATIVE';
    const FAMILLE_NEGOCIANT_VINIFICATEUR = 'NEGOCIANT_VINIFICATEUR';
    const FAMILLE_APPORTEUR_NEGOCE_TOTAL = 'APPORTEUR_NEGOCE_TOTAL';
    const FAMILLE_APPORTEUR_COOP_TOTAL = 'APPORTEUR_COOP_TOTAL';
    const FAMILLE_CAVE_PARTICULIERE_TOTAL = 'CAVE_PARTICULIERE_TOTAL';
    const FAMILLE_APPORTEUR_COOP_ET_NEGOCE = 'APPORTEUR_COOP_ET_NEGOCE';
    const FAMILLE_CAVE_PARTICULIERE_ET_APPORTEUR_COOP = 'CAVE_PARTICULIERE_ET_APPORTEUR_COOP';
    const FAMILLE_CAVE_PARTICULIERE_ET_APPORTEUR_NEGOCE = 'CAVE_PARTICULIERE_ET_APPORTEUR_NEGOCE';
    const FAMILLE_CAVE_PARTICULIERE_ET_APPORTEUR_COOP_ET_NEGOCE = 'CAVE_PARTICULIERE_ET_APPORTEUR_COOP_ET_NEGOCE';
    const FAMILLE_SANS_VOLUME = 'SANS_VOLUME';


    protected $mouvement_document = null;
    protected $declarant_document = null;
    protected $enhanced_donnees = null;


    public function getPeriode() {

        return $this->campagne;
    }

    public function getCampagneReelle()
    {
        return sprintf("%s-%s", $this->campagne, $this->campagne + 1);
    }

    public function __clone() {
		parent::__clone();
	}

    protected function initDocuments() {
        parent::initDocuments();
        $this->mouvement_document = new MouvementFacturesDocument($this);
        $this->declarant_document = new DeclarantDocument($this);
    }

    public function getRegions()
    {
        if (!RegionConfiguration::getInstance()->hasOdgProduits()) {
            return [];
        }
        $regions = array();
        foreach(RegionConfiguration::getInstance()->getOdgRegions() as $region) {
            foreach($this->getProduits() as $produit_hash => $p) {
                if (RegionConfiguration::getInstance()->isHashProduitInRegion($region, $produit_hash)) {
                    $regions[] = $region;
                    break;
                }
            }
        }
        return $regions;
    }

    public function save() {
        if (RegionConfiguration::getInstance()->hasOdgProduits()) {
            $regions = $this->getRegions();
            if (count($regions)) {
                $this->add('region', implode('|', $regions));
            }
        }
        if(DRevConfiguration::getInstance()->isRevendicationParLots()){
            if(!$this->exist('donnees') || !count($this->donnees)) {
                   $this->generateDonnees();
            }

            if(!$this->isFactures()){
                $this->clearMouvementsFactures();
                $this->generateMouvementsFactures();
            }
        }

        parent::save();

    }

    /**** DECLARANT ****/
    public function storeDeclarant() {
        $this->declarant_document->storeDeclarant();

        if($this->getEtablissementObject()->famille) {
            $this->declarant->famille = $this->getEtablissementObject()->famille;
        }
    }

    /**** MOUVEMENTS ****/

    public function getTemplateFacture() {
        return TemplateFactureClient::getInstance()->findByCampagne($this->getCampagne());
    }

    public function getMouvementsFactures() {

        return $this->_get('mouvements');
    }

    public function getMouvementsFacturesCalcule() {

      $templateFacture = $this->getTemplateFacture();

      if(!$templateFacture) {
          return array();
      }

      $drev = DRevClient::getInstance()->findMasterByIdentifiantAndPeriode($this->identifiant, $this->getPeriode());

      // TODO : pour l'instant cela est géré avec le critère isRevendication par lot
      if($this->getTotalValeur("15") && !$drev && !DRevConfiguration::getInstance()->isRevendicationParLots()){
          throw new FacturationPassException("L15 et pas de Drev : ".$this->_id." on skip la facture");
      }

      $cotisations = $templateFacture->generateCotisations($this);

      if($this->hasVersion()) {
          $cotisationsPrec = $templateFacture->generateCotisations($this->getMother());
      }

      $identifiantCompte = $this->getIdentifiant();

      $mouvements = array();

      $rienAFacturer = true;

      $classMouvement = get_class($this)."MouvementFactures";

      foreach($cotisations as $cotisation) {
          $mouvement = $classMouvement::freeInstance($this);
          $mouvement->createFromCotisationAndDoc($cotisation,$this);
          $mouvement->date = $this->getPeriode().'-12-10';
          $mouvement->date_version = $this->getPeriode().'-12-10';

          if(isset($cotisationsPrec[$cotisation->getHash()])) {
              $mouvement->quantite = $mouvement->quantite - $cotisationsPrec[$cotisation->getHash()]->getQuantite();
          }

          if(!$mouvement->quantite) {
              continue;
          }

          if($mouvement->quantite) {
              $rienAFacturer = false;
          }

          $mouvements[$mouvement->getMD5Key()] = $mouvement;
      }

      if($rienAFacturer) {
          return array();

      }

      return array($identifiantCompte => $mouvements);
    }

    public function getMouvementsFacturesCalculeByIdentifiant($identifiant) {

        return $this->mouvement_document->getMouvementsFacturesCalculeByIdentifiant($identifiant);
    }

    public function generateMouvementsFactures() {

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

    public function hasVersion() {

        return false;
    }

    public function getVersion() {

        return null;
    }

    public function getCsv() {
        $classExport = DeclarationClient::getInstance()->getExportCsvClassName($this->type);
        $export = new $classExport($this, false);

        return $export->getCsv();
    }

    public function generateDonnees() {
        if (!$this->exist('donnees') || count($this->donnees) < 1) {
            $this->add('donnees');
            $generate = false;
            foreach ($this->getCsv() as $datas) {
                $this->addDonnee($datas);
            }
        }
        return false;
    }

    private $bailleuretablissements = null;
    public function getBailleurEtablissement($id){
        if (is_null($this->bailleuretablissements)) {
            $this->bailleuretablissements = array();
        }
        if (!isset($this->bailleuretablissements[$id])) {
            $this->bailleuretablissements[$id] = EtablissementClient::getInstance()->find($id);
        }
        return $this->bailleuretablissements[$id];
    }

    public function getEnhancedDonnees($drev_produit_filter = null) {
        if (isset($this->enhanced_donnees)) {
            return $this->enhanced_donnees;
        }
        $this->generateDonnees();

        foreach (ChgtDenomClient::getInstance()->getChgtDenomProduction($this->identifiant, $this->campagne) as $chgt) {
            $chgt->addDonneesForProduction($this);
        }

        $this->enhanced_donnees = array();
        $colonnesid = array();
        $colonneid = 0;
        $drev = DRevClient::getInstance()->retrieveRelatedDrev($this->identifiant, $this->getCampagne());
        foreach($this->donnees as $i => $donnee) {
            $d = (object) $donnee->toArray();
            $d->produit_conf = $this->configuration->declaration->get($donnee->produit);
            $p = array();
            if ($donnee->bailleur && $d->bailleur_etablissement = $this->getBailleurEtablissement($donnee->bailleur)) {
                $p[] = $d->bailleur_etablissement->raison_sociale.' ('.$donnee->bailleur.')';
                $p[] = $d->bailleur_etablissement->ppm;
            } else {
                $p[] = $donnee->bailleur_raison_sociale;
                $p[] = $donnee->bailleur_ppm;
            }
            $p[] = $d->produit_conf->getCertification()->getKey();
            $p[] = $d->produit_conf->getGenre()->getKey();
            $p[] = $d->produit_conf->getAppellation()->getKey();
            $p[] = $d->produit_conf->getMention()->getKey();
            $p[] = $d->produit_conf->getLieu()->getKey();
            $p[] = $d->produit_conf->getCouleur()->getKey();
            $p[] = $d->produit_conf->getCepage()->getKey();
            $p[] = $d->produit_conf->code_douane;
            $p[] = $d->produit_conf->getLibelleFormat();
            $p[] = $donnee->complement;
            $d->produit_csv = $p;
            $produitid = join("", $p);
            if ($donnee->colonneid) {
                $colonnesid[$produitid] = $donnee->colonneid;
                if ($colonneid < $donnee->colonneid) {
                    $colonneid = $donnee->colonneid;
                }
                $colonneid = $donnee->colonneid;
            }else{
                if (!isset($colonnesid[$produitid]) || !$colonnesid[$produitid] || ($colonnesid[$produitid] < $colonneid) || $d->categorie == '04') {
                    $colonnesid[$produitid] = ++$colonneid;
                }
                $donnee->colonneid = $colonnesid[$produitid];
            }
            $d->colonneid = $donnee->colonneid;
            $d->drev_produit_filter = null;
            if ($drev && $drev->hasLotsProduitFilter($donnee->produit)) {
                $d->drev_produit_filter = 'FILTERED:'.$drev->_id;
            }
            if ($drev) {
                $d->drev_id = $drev->_id;
            }else{
                $d->drev_id = null;
            }
            $this->enhanced_donnees[] = $d;
        }
        $this->enhancedDonnneesWithFamille();
        return $this->enhanced_donnees;
    }

    protected function enhancedDonnneesWithFamille(){
        $has_volume_cave = false;
        $has_volume_cave_lignes = array();
        $has_volume_nego = false;
        $has_volume_nego_lignes = array();
        $has_volume_coop = false;
        $has_volume_coop_lignes = array();
        $colonne_keys = array();
        foreach ($this->enhanced_donnees as $donnee) {
            $key = $donnee->colonneid.$donnee->bailleur_ppm.$donnee->bailleur_raison_sociale;
            switch ($donnee->categorie) {
                case '09':
                    $has_volume_cave = true;
                    $has_volume_cave_lignes[$key] = true;
                    break;
                case '08':
                    $has_volume_coop = true;
                    $has_volume_coop_lignes[$key] = true;
                    break;
                case '07':
                case '06':
                    $has_volume_nego = true;
                    $has_volume_nego_lignes[$key] = true;
                    break;
            }
            $colonne_keys[] = $key;
        }
        $famille = $this->getFamilleCalculeeFromLigneDouane($has_volume_cave, $has_volume_coop, $has_volume_nego);
        $familles_lignes = array();
        foreach($colonne_keys as $i) {
            $familles_lignes[$i] = $this->getFamilleCalculeeFromLigneDouane(@$has_volume_cave_lignes[$i], @$has_volume_coop_lignes[$i], @$has_volume_nego_lignes[$i]);
        }
        foreach($this->enhanced_donnees as $donnee) {
            $donnee->document_famille = $famille;
            $donnee->colonne_famille = $familles_lignes[$donnee->colonneid.$donnee->bailleur_ppm.$donnee->bailleur_raison_sociale];
        }
    }

    public function getFamilleCalculeeFromLigneDouane($has_volume_cave = false, $has_volume_coop = false, $has_volume_nego = false) {
        return self::getFamilleCalculeeFromTypeAndLigneDouane($this->type, $has_volume_cave, $has_volume_coop, $has_volume_nego);
    }

    public static function getFamilleCalculeeFromTypeAndLigneDouane($type, $has_volume_cave = false, $has_volume_coop = false, $has_volume_nego = false) {
            if ($type == 'SV11') {
                return DouaneProduction::FAMILLE_COOPERATIVE;
            }
            if ($type == 'SV12') {
                return DouaneProduction::FAMILLE_NEGOCIANT_VINIFICATEUR;
            }
            $famille = '';
            if ($has_volume_nego && !$has_volume_coop && !$has_volume_cave) {
                $famille = DouaneProduction::FAMILLE_APPORTEUR_NEGOCE_TOTAL;
            }elseif (!$has_volume_nego && $has_volume_coop && !$has_volume_cave) {
                $famille = DouaneProduction::FAMILLE_APPORTEUR_COOP_TOTAL;
            }elseif (!$has_volume_nego && !$has_volume_coop && $has_volume_cave) {
                $famille = DouaneProduction::FAMILLE_CAVE_PARTICULIERE_TOTAL;
            }elseif ($has_volume_nego && $has_volume_coop && !$has_volume_cave) {
                $famille = DouaneProduction::FAMILLE_APPORTEUR_COOP_ET_NEGOCE;
            }elseif (!$has_volume_nego && $has_volume_coop && $has_volume_cave) {
                $famille = DouaneProduction::FAMILLE_CAVE_PARTICULIERE_ET_APPORTEUR_COOP;
            }elseif ($has_volume_nego && !$has_volume_coop && $has_volume_cave) {
                $famille = DouaneProduction::FAMILLE_CAVE_PARTICULIERE_ET_APPORTEUR_NEGOCE;
            }elseif ($has_volume_nego && $has_volume_coop && $has_volume_cave) {
                $famille = DouaneProduction::FAMILLE_CAVE_PARTICULIERE_ET_APPORTEUR_COOP_ET_NEGOCE;
            }elseif (!$has_volume_nego && !$has_volume_coop && !$has_volume_cave) {
                $famille = DouaneProduction::FAMILLE_SANS_VOLUME;
            }else{
                throw new sfException("Cas de famille DR non gérée (".$this->getCsvType()." ; ".boolval($has_volume_nego)." ; ".boolval($has_volume_coop)." ; ".boolval($has_volume_cave).")");
            }
            return $famille;
        }



    public function addDonnee($data) {

        if (!$data || !isset($data[DouaneCsvFile::CSV_PRODUIT_CERTIFICATION]) || empty($data[DouaneCsvFile::CSV_PRODUIT_CERTIFICATION])) {
            return null;
        }

        $hash = "certifications/".$data[DouaneCsvFile::CSV_PRODUIT_CERTIFICATION]."/genres/".$data[DouaneCsvFile::CSV_PRODUIT_GENRE]."/appellations/".$data[DouaneCsvFile::CSV_PRODUIT_APPELLATION]."/mentions/".$data[DouaneCsvFile::CSV_PRODUIT_MENTION]."/lieux/".$data[DouaneCsvFile::CSV_PRODUIT_LIEU]."/couleurs/".$data[DouaneCsvFile::CSV_PRODUIT_COULEUR]."/cepages/".$data[DouaneCsvFile::CSV_PRODUIT_CEPAGE];

        if(!$this->getConfiguration()->declaration->exist($hash)) {
            return null;
        }

        $this->add('donnees');
        $item = $this->get('donnees')->add();
        if ($this->isBailleur()) {
            $item->add('declarant_identifiant', $data[DouaneCsvFile::CSV_RECOLTANT_ID]);
            $item->add('declarant_cvi', $data[DouaneCsvFile::CSV_RECOLTANT_CVI]);
            $item->add('declarant_raison_sociale', $data[DouaneCsvFile::CSV_RECOLTANT_LIBELLE]);
        }
        $item->produit = $hash;
        $item->produit_libelle = $this->getConfiguration()->declaration->get($hash)->getLibelleComplet();
        $item->complement = $data[DouaneCsvFile::CSV_PRODUIT_COMPLEMENT];
        $item->categorie = $data[DouaneCsvFile::CSV_LIGNE_CODE];
        $item->categorie_libelle = $data[DouaneCsvFile::CSV_LIGNE_LIBELLE];
        $item->valeur = VarManipulator::floatize($data[DouaneCsvFile::CSV_VALEUR]);
        $item->colonneid = $data[DouaneCsvFile::CSV_COLONNE_ID];
        self::fillItemWithTiersData($item, $data[DouaneCsvFile::CSV_TIERS_CVI], $data[DouaneCsvFile::CSV_TIERS_LIBELLE]);
        if ($data[DouaneCsvFile::CSV_BAILLEUR_PPM]) {
            if ($tiers = EtablissementClient::getInstance()->findByPPM($data[DouaneCsvFile::CSV_BAILLEUR_PPM])) {
                $item->bailleur = $tiers->_id;
                $item->bailleur_ppm = $tiers->ppm;
                $item->bailleur_raison_sociale = $tiers->raison_sociale;

            }else{
                $item->bailleur_ppm = $data[DouaneCsvFile::CSV_BAILLEUR_PPM];
                $item->bailleur_raison_sociale = $data[DouaneCsvFile::CSV_BAILLEUR_NOM];
            }
        }

        return $item;
    }

    protected static $cvi2tiers = null;
    public static function fillItemWithTiersData(&$item, $tiers_cvi, $tiers_libelle) {
        if (!self::$cvi2tiers) {
            self::$cvi2tiers = array();
        }
        if ($tiers_cvi) {
            $tiers_cvi = str_replace('"', '', $tiers_cvi);
            if (!isset(self::$cvi2tiers[$tiers_cvi])) {
                self::$cvi2tiers[$tiers_cvi] = EtablissementClient::getInstance()->findByCvi($tiers_cvi);
            }
            $tiers = self::$cvi2tiers[$tiers_cvi];
            if ($tiers) {
                $item->tiers = $tiers->_id;
                $item->tiers_raison_sociale = $tiers->raison_sociale;
                $item->tiers_cvi = str_replace('"', '', $tiers->cvi);
                $item->tiers_commune = $tiers->siege->commune;
            } else {
                $item->tiers_raison_sociale = preg_replace('/(^"|"$)/', '', $tiers_libelle);
                $item->tiers_cvi = $tiers_cvi;
                $item->tiers_commune = null;
            }
        }
    }

    public function getCategorie(){
        return strtolower($this->type);
    }

    public function calcul($formule, TemplateFactureCotisationCallbackParameters $produitFilter) {
        $calcul = $formule;
        $numLignes = preg_split('|[\-+*\/() ]+|', $formule, -1, PREG_SPLIT_NO_EMPTY);
        foreach($numLignes as $numLigne) {
            $datas[$numLigne] = $this->getTotalValeur($numLigne, null, $produitFilter);
        }

        foreach($datas as $numLigne => $value) {
            $calcul = str_replace($numLigne, $value, $calcul);
        }

        return eval("return $calcul;");
    }

    public function matchFilter($produit, TemplateFactureCotisationCallbackParameters $produitFilter)
    {
        $match = true;
        $etablissements = [];

        if ($produitFilter === null) {
            $produitFilter = [];
        }else{
            $produitFilter = $produitFilter->getParameters();
        }
        foreach ($produitFilter as $type => $filter) {
            if ($type === 'appellations') {
                $match = $match && $this->matchFilterProduit($produit, $filter);
            } elseif ($type === 'millesime') {
                $match = $match && $this->matchFilterMillesime($produit, $filter);
            } elseif ($type === 'deja') {
                // On gère que l'option (NOT)? /deja/CONFORME pour le moment
                // Pas NONCONFORME
                $match = $match && $this->matchFilterConformite($produit, $filter);
            } elseif ($type === 'region') {
                $region = $filter;
                $match = $match && RegionConfiguration::getInstance()->isHashProduitInRegion($region, $produit->produit);
            } elseif($type === 'famille') {
                $match = $match && DRevClient::getInstance()->matchFilterFamille($this->declarant->famille, $filter);
            }
        }

        return $match;
    }

    public function matchFilterProduit($produit, $produitFilter) {
        $produitFilter = preg_replace("/^NOT /", "", $produitFilter, -1, $produitExclude);
        $produitExclude = (bool) $produitExclude;
        $regexpFilter = "#(".implode("|", explode(",", $produitFilter)).")#";

        if($produitFilter && !$produitExclude && !preg_match($regexpFilter, $produit)) {

            return false;
        }
        if($produitFilter && $produitExclude && preg_match($regexpFilter, $produit)) {

            return false;
        }

        return true;
    }


    public function getTotalValeur($numLigne, $familles = null, TemplateFactureCotisationCallbackParameters $produitFilter = null, $famille_exclue = null, $throw_familles = array(), $metayer_vrai_bailleur_faux = true) {
        $value = 0;
        foreach($this->getEnhancedDonnees() as $donnee) {
            if (in_array($donnee->colonne_famille, $throw_familles)) {
                throw new sfException("Famille $donnee->colonne_famille non permise");
            }
            if ($familles && !in_array($donnee->colonne_famille, $familles)) {
                continue;
            }
            if ($famille_exclue && $donnee->colonne_famille == $famille_exclue) {
                continue;
            }
            if($produitFilter && !$this->matchFilter($donnee, $produitFilter)) {
                continue;
            }
            if(preg_replace('/^0/', '', $donnee->categorie) !== preg_replace('/^0/', '', str_replace("L", "", $numLigne))) {
                continue;
            }
            if ($metayer_vrai_bailleur_faux && $donnee->bailleur_raison_sociale) {
                continue;
            }
            if (!$metayer_vrai_bailleur_faux && !($donnee->bailleur_ppm == $this->declarant->ppm)) {
                continue;
            }
            $value = $value + VarManipulator::floatize($donnee->valeur);
        }


        if ($produitFilter) {
            $round_methode = $produitFilter->getParameters('round_methode');
            if ($round_methode) {
                $value = $round_methode($value);
            }
        }

        return $value;
    }

    public function getDonnees() {
        //Si les tiers ne s'affiche pas, supprimer le champ données en base
        if (!$this->exist('donnees')) {
            $this->generateDonnees();
        }
        return $this->_get('donnees');
    }

    public function getNbApporteurs($produitFilter = null) {
        $apporteurs = array();
        foreach($this->getDonnees() as $donnee) {
            if (!$donnee->tiers_cvi) {
                continue;
            }
            if ($produitFilter && !$this->matchFilterProduit($donnee->produit, $produitFilter)) {
                continue;
            }
            $apporteurs[$donnee->tiers_cvi] = 1;
        }
        return count($apporteurs);
    }

    public function getProduits()
    {
        $donnees = [];

        foreach ($this->getEnhancedDonnees() as $ligne) {
            $produit_key = $ligne->produit;

            if (array_key_exists($produit_key, $donnees) === false) {
                $donnees[$produit_key] = [];
                $donnees[$produit_key]['libelle'] = $ligne->produit_libelle;
                $donnees[$produit_key]['hash'] = $produit_key;
                $donnees[$produit_key]['lignes'] = [];
            }

            if (array_key_exists($ligne->categorie, $donnees[$produit_key]['lignes']) === false) {
                $donnees[$produit_key]['lignes'][$ligne->categorie]['val'] = 0;
                if (in_array($ligne->categorie, ['04', '04b'])) {
                    $unit = 'ha';
                    $decimals = 4;
                } else {
                    $unit = 'hl';
                    $decimals = 2;
                }
                $donnees[$produit_key]['lignes'][$ligne->categorie]['unit'] = $unit;
                $donnees[$produit_key]['lignes'][$ligne->categorie]['decimals'] = $decimals;
            }

            $donnees[$produit_key]['lignes'][$ligne->categorie]['val'] += str_replace(',', '.', $ligne->valeur);
        }

        return $donnees;
    }

    public function getProduitsSynthese()
    {
        $synthese = [];
        foreach ($this->getProduits() as $hash => $produit) {
            $hash_produit = strstr($hash, '/cepages', true);
            if (array_key_exists($hash_produit, $synthese) === false) {
                $synthese[$hash_produit] = [];
                $synthese[$hash_produit]['lignes'] = [];
                $synthese[$hash_produit]['libelle'] = $this->getConfiguration()->declaration->get($hash)->getCouleur()->getLibelleComplet();
            }

            foreach ($produit['lignes'] as $ligne => $value) {
                if (array_key_exists($ligne, $synthese[$hash_produit]['lignes']) === false) {
                    $synthese[$hash_produit]['lignes'][$ligne] = [];
                    $synthese[$hash_produit]['lignes'][$ligne]['val'] = 0;
                    $synthese[$hash_produit]['lignes'][$ligne]['unit'] = (in_array($ligne, ['04', '04b'])) ? 'ha' : 'hl';
                    $synthese[$hash_produit]['lignes'][$ligne]['decimals'] = (in_array($ligne, ['04', '04b'])) ? 4 : 2;
                }

                $synthese[$hash_produit]['lignes'][$ligne]['val'] += $value['val'];
            }
        }

        return $synthese;
    }

    public function isBailleur() {
        return $this->exist('has_metayers');
    }

    public function getProduitsDetail()
    {
        $donnees = [];


        // Produits :
        $donnees['lignes'] = ['04', '04b', '05', '06', '07', '08', '09', '15', '16', '18', '19'];
        $donnees['produits'] = [];
        foreach ($this->getEnhancedDonnees() as $entry) {
            if($entry->bailleur_ppm && !$this->isBailleur()) {
                continue;
            }
            if($this->isBailleur() && $entry->bailleur_ppm != $this->declarant->ppm) {
                continue;
            }
            $produit = $entry->produit;
            if ($this->isBailleur()) {
                $produit .= $entry->declarant_cvi;
            }
            if (DRevConfiguration::getInstance()->hasImportDRWithMentionsComplementaire() && $entry->complement) {
                $produit .= ' '.$entry->complement;
            }
            $categorie = $entry->categorie;
            if (in_array($categorie, $donnees['lignes']) === false) {
                continue;
            }

            if (array_key_exists($produit, $donnees['produits']) === false) {
                $donnees['produits'][$produit]['lignes'] = [];
                $donnees['produits'][$produit]['libelle'] = $this->getConfiguration()->declaration->get($entry->produit)->getCepage()->getLibelleComplet();
                if (DRevConfiguration::getInstance()->hasImportDRWithMentionsComplementaire() && $entry->complement) {
                    $donnees['produits'][$produit]['libelle'] .= ' - '.$entry->complement;
                    $donnees['produits'][$produit]['complement'] = $entry->complement;
                }
                $donnees['produits'][$produit]['hash'] = $entry->produit;
                if ($this->isBailleur()) {
                    $donnees['produits'][$produit]['metayers'] = array(
                                        'declarant_identifiant' => $entry->declarant_identifiant,
                                        'declarant_cvi' => $entry->declarant_cvi,
                                        'declarant_raison_sociale' => str_replace('"', '', $entry->declarant_raison_sociale)
                                    );
                }
            }

            if (array_key_exists($categorie, $donnees['produits'][$produit]['lignes']) === false) {
                $donnees['produits'][$produit]['lignes'][$categorie] = [];
                $donnees['produits'][$produit]['lignes'][$categorie]['val'] = 0;
            }

            $donnees['produits'][$produit]['lignes'][$categorie]['val'] += str_replace(',', '.', $entry->valeur);
            $donnees['produits'][$produit]['lignes'][$categorie]['unit'] = (in_array($entry->categorie, ['04', '04b'])) ? 'ha' : 'hl';
            $donnees['produits'][$produit]['lignes'][$categorie]['decimals'] = (in_array($entry->categorie, ['04', '04b'])) ? 4 : 2;
        }

        // potentiellement, des lignes n'existent pas pour certains produits
        foreach ($donnees['produits'] as $key => &$value) {
            $missing = array_diff($donnees['lignes'], array_keys($value['lignes']));
            if (count($missing)) {
                foreach ($missing as $k => $m) {
                    $value['lignes'][$m] = ['val' => null];
                    $value['lignes'][$m]['unit'] = (in_array($m, ['04', '04b'])) ? 'ha' : 'hl';
                }
            }
        }

        ksort($donnees['produits']);
        foreach ($donnees['produits'] as &$array) {
            ksort($array['lignes'], SORT_STRING);
        }

        return $donnees;
    }


	public function getConfiguration() {

		return ConfigurationClient::getConfiguration($this->campagne.'-12-10');
	}

    public function switchEnAttente()
    {
        if (! $this->exist('statut_odg')) {
            $this->add('statut_odg', null);
        }

        $this->statut_odg = ($this->statut_odg) ? null : DRClient::STATUT_EN_ATTENTE;
    }

    public function validateOdg($date = null)
    {
        $this->add('validation_odg');
        $this->validation_odg = ($date) ?: date('Y-m-d');

        if ($this->exist('statut_odg') && $this->statut_odg === DRClient::STATUT_EN_ATTENTE) {
            $this->statut_odg = null;
        }
    }

    public function isValideeOdg() {
        if (DRConfiguration::getInstance()->hasValidationDR()) {
            return $this->exist('validation_odg') && ($this->validation_odg);
        }
        return false;
    }

    public function isDeletable() {
        if ($this->exist('validation_odg') && $this->validation_odg) {
            return false;
        }
        if ($this->exist('statut_odg') && $this->statut_odg) {
            return false;
        }
        if ($this->exist('mouvements') && count($this->mouvements) ) {
            return false;
        }
        return true;
    }

    public function getBailleurs($cave_particuliere_only = false) {
        $csv = $this->getCsv();
      if (!$csv) {
        return array();
      }

        return DouaneProduction::getBailleursFromCsv($this->getEtablissementObject(), $csv, $this->getConfiguration(), $cave_particuliere_only);
    }

    public static function getBailleursFromCsv($etablissement, $csv, $configuration, $cave_particuliere_only = false) {
        $etablissementBailleursRelations = array();
        foreach($etablissement->getMeAndLiaisonOfType(EtablissementClient::TYPE_LIAISON_BAILLEUR) as $etablissementBailleur) {
            if(!$etablissementBailleur->ppm) {
                continue;
            }
            if(!$etablissementBailleur->exist('liaisons_operateurs/METAYER_'.$etablissement->_id)) {
                continue;
            }
            $etablissementBailleursRelations[$etablissementBailleur->ppm] = $etablissementBailleur;
        }


        $etablissementBailleurCache = $etablissementBailleursRelations;
        $bailleurs = array();
        foreach($csv as $line) {
            $produitConfig = $configuration->findProductByCodeDouane($line[DRCsvFile::CSV_PRODUIT_INAO]);
            if(!$produitConfig) {
                continue;
            }
            if (!$produitConfig->isActif()) {
                continue;
            }
            if($line[DRCsvFile::CSV_RECOLTANT_ID] != $etablissement->identifiant) {
                continue;
            }

            if($line[DouaneCsvFile::CSV_TYPE] != DRCsvFile::CSV_TYPE_DR) {
                continue;
            }

            $ppm = $line[DRCsvFile::CSV_BAILLEUR_PPM];

            if(!trim($ppm)) {
                continue;
            }

            if ($cave_particuliere_only && ($line[DRCsvFile::CSV_LIGNE_CODE] != DRCsvFile::CSV_LIGNE_CODE_VOLUME_L9 || !trim($line[DRCsvFile::CSV_VALEUR])) ) {
                continue;
            }

            $etablissement_id = null;
            $id = $ppm;
            if(isset($etablissementBailleurCache[$ppm])) {
                $etablissement_id = $etablissementBailleurCache[$ppm]->_id;
            }
            if (!$etablissement_id && $etablissement_bailleur = EtablissementClient::getInstance()->findByPPM($ppm)) {
                $etablissement_id = $etablissement_bailleur->_id;
                $etablissementBailleurCache[$ppm] = $etablissement_bailleur;
            }
            if($etablissement_id) {
                $id = $etablissement_id;
            }
            $bailleurs[$id]  = array(
                'raison_sociale' => $line[DRCsvFile::CSV_BAILLEUR_NOM],
                'etablissement_id' => $etablissement_id,
                'ppm' => $ppm,
                'relation_exist' => isset($etablissementBailleursRelations[$ppm])
            );
        }
        return $bailleurs;
    }

    public function getMetayers($cave_particuliere_only = false) {
        $csv = $this->getCsv();
      if (!$csv) {
        return array();
      }

        return DouaneProduction::getMetayersFromCsv($csv, $this->getConfiguration(), $cave_particuliere_only);
    }

    public function getMetayersFromCsv($csv, $configuration, $cave_particuliere_only = false) {
        $etablissement = EtablissementClient::getInstance()->findByPPM($this->declarant->ppm);

        $etablissementMetayersRelations = array();
        foreach($etablissement->getMeAndLiaisonOfType(EtablissementClient::TYPE_LIAISON_METAYER) as $etablissementMetayer) {
            if(!$etablissementMetayer->exist('liaisons_operateurs/BAILLEUR_'.$etablissement->_id)) {
                continue;
            }
            $etablissementMetayersRelations[$etablissementMetayer->cvi] = $etablissementMetayer;
        }

        $etablissementMetayerCache = $etablissementMetayersRelations;
        $metayers = array();
        foreach($csv as $line) {
            $produitConfig = $configuration->findProductByCodeDouane($line[DRCsvFile::CSV_PRODUIT_INAO]);
            if(!$produitConfig) {
                continue;
            }
            if (!$produitConfig->isActif()) {
                continue;
            }
            if($line[DRCsvFile::CSV_BAILLEUR_PPM] != $this->declarant->ppm) {
                continue;
            }

            if($line[DouaneCsvFile::CSV_TYPE] != DRCsvFile::CSV_TYPE_DR) {
                continue;
            }

            $cvi = str_replace('"', '', $line[DRCsvFile::CSV_RECOLTANT_CVI]);

            if(!trim($cvi)) {
                continue;
            }

            if ($cvi == $etablissement->cvi) {
                continue;
            }

            if ($cave_particuliere_only && ($line[DRCsvFile::CSV_LIGNE_CODE] != DRCsvFile::CSV_LIGNE_CODE_VOLUME_L9 || !trim($line[DRCsvFile::CSV_VALEUR])) ) {
                continue;
            }

            $etablissement_id = null;
            $id = $cvi;
            if(isset($etablissementMetayerCache[$cvi])) {
                $etablissement_id = $etablissementMetayerCache[$cvi]->_id;
            }
            if (!$etablissement_id && $etablissement_metayer = EtablissementClient::getInstance()->findByCvi($cvi)) {
                $etablissement_id = $etablissement_metayer->_id;
                $etablissementMetayerCache[$cvi] = $etablissement_metayer;
            }
            if($etablissement_id) {
                $id = $etablissement_id;
            }
            $metayers[$id]  = array(
                'raison_sociale' => str_replace('"', '', $line[DRCsvFile::CSV_RECOLTANT_LIBELLE]),
                'etablissement_id' => $etablissement_id,
                'cvi' => $cvi,
                'relation_exist' => isset($etablissementMetayersRelations[$cvi])
            );
        }
        return $metayers;
    }


    public function hasApporteurs($include_non_reconnu = false) {
        return count($this->getApporteurs($include_non_reconnu));
    }

    public function isApporteur($include_non_reconnu = false) {
        if ($this->getDocumentDefinitionModel() != 'DR') {
            return false;
        }
        return count($this->getTiers($include_non_reconnu));
    }

    public function getApporteurs($include_non_reconnu = false, $hydrate = acCouchdbClient::HYDRATE_JSON): array {
        if ($this->getDocumentDefinitionModel() == 'DR') {
            return array();
        }
        return $this->getTiers($include_non_reconnu);
    }

    public function getTiers($include_non_reconnu = false, $relation_voulue = null, $hydrate = acCouchdbClient::HYDRATE_JSON): array {
        $cvis = array();
        foreach($this->getCsv() as $data) {
            $cvi = $data[DouaneCsvFile::CSV_TIERS_CVI];
            $cvi = str_replace('"', '', $cvi);
            if(!$cvi) {
                continue;
            }
            if(isset($cvis[$cvi])) {
                continue;
            }
            if ($relation_voulue != null &&
                    $relation_voulue == EtablissementFamilles::FAMILLE_NEGOCIANT_VINIFICATEUR &&
                        (! ($data[DouaneCsvFile::CSV_LIGNE_CODE] === "06" ||
                            $data[DouaneCsvFile::CSV_LIGNE_CODE] === "07"))) {
                continue;
            }
            if ($relation_voulue != null &&
                    $relation_voulue == EtablissementFamilles::FAMILLE_COOPERATIVE &&
                        (! ($data[DouaneCsvFile::CSV_LIGNE_CODE] === "09"))) {
                continue;
            }
            $etablissement = EtablissementClient::getInstance()->findByCvi($cvi);
            if(!$etablissement) {
                $cvis[$cvi] = $data[DouaneCsvFile::CSV_TIERS_LIBELLE];
                continue;
            }

            $cvis[$cvi] = $etablissement;
        }

        $etablissements = array();
        foreach($cvis as $cvi => $etablissement) {
            if(is_string($etablissement)) {
                if ($include_non_reconnu) {
                    $etablissements[$cvi] = array('etablissement' => null , "cvi" => $cvi, 'raison_sociale' => $etablissement);
                    $etablissements[$cvi] = str_replace('"', '', $etablissements[$cvi]);
                }
                continue;
            }
            $etablissements[$etablissement->_id] = array('etablissement' => $etablissement, 'cvi' => $cvi, 'raison_sociale' => $etablissement->raison_sociale);
        }

        return $etablissements;
    }

    public function getDateDocument() {
        return $this->getPeriode() .  '-12-10';
    }

    public function getTableauComparaisonSV11() {
        if (! ($this->type == 'SV11')) {
            throw new sfException("Ce document n'est pas une SV11.");
        }
        $produits = $this->getProduits();
        $etab_declarant =  $this->getEtablissementObject();
        foreach ($this->getDonnees() as $data) {
            if (($data->categorie == '06' || $data->categorie == '07' || $data->categorie == '08')) {
                if (! isset($tableau_comparaison[$data->produit_libelle][$data->tiers_cvi]['SV'])) {
                    $tableau_comparaison[$data->produit_libelle][$data->tiers_cvi]['SV'] = $data->valeur;
                    $tableau_comparaison[$data->produit_libelle][$data->tiers_cvi]['DR'] = 0;
                } else {
                    $tableau_comparaison[$data->produit_libelle][$data->tiers_cvi]['SV'] += $data->valeur;
                }
                if(! isset($tableau_comparaison[$data->produit_libelle][$etab_declarant->cvi]['SV'])) {
                    $tableau_comparaison[$data->produit_libelle][$etab_declarant->cvi]['SV'] = $produits[$data->produit]['lignes'][$data->categorie]['val'];
                }
            }
        }
        foreach ($tableau_comparaison as $produit_libelle => $cvis) {
            $totalDR = 0;
            foreach ($cvis as $cvi => $valeur) {
                if ($etab_declarant->cvi == $cvi) {
                    continue;
                }
                $dr = null;
                $etab = EtablissementClient::getInstance()->findByCvi($cvi);
                if ($etab) {
                    $dr = DRClient::getInstance()->find('DR-'.$etab['identifiant'].'-'.$this->campagne);
                }

                if (! $dr) {
                    $tableau_comparaison[$produit_libelle][$cvi]['DR'] = 0;
                } else {
                    $datas = $dr->getEnhancedDonnees();
                    foreach ($datas as $data) {
                        if ($data->tiers_cvi != $etab_declarant->cvi && substr($data->tiers, 0, -2) != substr($etab_declarant->_id, 0, -2)) {
                            continue;
                        }
                        if ($data->produit_libelle != $produit_libelle && $dr->getConfiguration()->declaration->get($data->produit)->getLibelleFormat() != $produit_libelle) {
                            continue;
                        }
                        if ($data->categorie == '06' || $data->categorie == '07' || $data->categorie == '08') {
                            if (! isset($tableau_comparaison[$produit_libelle][$cvi]['DR'])) {
                                $tableau_comparaison[$produit_libelle][$cvi]['DR'] = $data->valeur;
                            } else {
                                $tableau_comparaison[$produit_libelle][$cvi]['DR'] += $data->valeur;
                            }
                            $totalDR += $data->valeur;
                        }
                    }
                }
            }
            $tableau_comparaison[$produit_libelle][$etab_declarant->cvi]['DR'] = $totalDR;
        }
        return isset($tableau_comparaison) ? $tableau_comparaison : null;
    }

    public function getTableauComparaisonSV12() {
        if (! ($this->type == 'SV12')) {
            throw new sfException("Ce document n'est pas une SV12.");
        }
        $tableau_comparaison = null;
        $produits = $this->getProduits();
        $etab_declarant =  $this->getEtablissementObject();
        foreach ($this->getDonnees() as $data) {
            if ($data->categorie == '15') {
                if (! isset($tableau_comparaison[$data->produit_libelle][$data->tiers_cvi]['SV'])) {
                    $tableau_comparaison[$data->produit_libelle][$data->tiers_cvi]['SV'] = $data->valeur;
                    $tableau_comparaison[$data->produit_libelle][$data->tiers_cvi]['DR'] = 0;
                } else {
                    $tableau_comparaison[$data->produit_libelle][$data->tiers_cvi]['SV'] += $data->valeur;
                }
                if(! isset($tableau_comparaison[$data->produit_libelle][$etab_declarant->cvi]['SV'])) {
                    $tableau_comparaison[$data->produit_libelle][$etab_declarant->cvi]['SV'] = $produits[$data->produit]['lignes'][$data->categorie]['val'];
                }
            }
        }
        if ($tableau_comparaison) {
            foreach ($tableau_comparaison as $produit_libelle => $cvis) {
                $totalDR = 0;
                foreach ($cvis as $cvi => $valeur) {
                    if ($etab_declarant->cvi == $cvi) {
                        continue;
                    }
                    $etab = EtablissementClient::getInstance()->findByCvi($cvi);
                    $dr = DRClient::getInstance()->find('DR-'.$etab['identifiant'].'-'.$this->campagne);
                    if (! $dr) {
                        $tableau_comparaison[$produit_libelle][$cvi]['DR'] = 0;
                    } else {
                        $datas = $dr->getEnhancedDonnees();
                        foreach ($datas as $data) {
                            if ($data->tiers_cvi != $etab_declarant->cvi && substr($data->tiers, 0, -2) != substr($etab_declarant->_id, 0, -2)) {
                                continue;
                            }
                            if ($data->produit_libelle != $produit_libelle && $dr->getConfiguration()->declaration->get($data->produit)->getLibelleFormat() != $produit_libelle) {
                                continue;
                            }
                            if ($data->categorie == '06' || $data->categorie == '07') {
                                if (! isset($tableau_comparaison[$produit_libelle][$cvi]['DR'])) {
                                    $tableau_comparaison[$produit_libelle][$cvi]['DR'] = $data->valeur;
                                } else {
                                    $tableau_comparaison[$produit_libelle][$cvi]['DR'] += $data->valeur;
                                }
                                $totalDR += $data->valeur;
                            }
                        }
                    }
                }
                $tableau_comparaison[$produit_libelle][$etab_declarant->cvi]['DR'] = $totalDR;
            }
        }
        return isset($tableau_comparaison) ? $tableau_comparaison : null;
    }

    public function getTableauComparaisonDrDap() {
        if ($this->type != 'DR') {
            throw new sfException("Ce document n'est pas une DR.");
        }
        $tableau_comparaison = [];
        $dap = ParcellaireAffectationClient::getInstance()->find('PARCELLAIREAFFECTATION-'.$this->identifiant.'-'.$this->campagne);
        if ($dap) {
            foreach ($dap->getParcelles(true) as $hash => $parcelle) {
                $libelle = $parcelle->getProduit()->getConfig()->getLibelleFormat();
                if (! isset($tableau_comparaison[$libelle])) {
                    $tableau_comparaison[$libelle]['DAP'] = $parcelle['superficie'];
                } else {
                    $tableau_comparaison[$libelle]['DAP'] += $parcelle['superficie'];
                }
                $total_valeur_dr = 0;
                $hash_cible = $parcelle->getProduit()->getHash();
                foreach ($this->getEnhancedDonnees() as $data) {
                    if (strpos($data->produit, str_replace('/declaration/', '', $hash_cible)) !== false && $data->categorie == '04b') {
                        $total_valeur_dr += $data->valeur;
                    }
                }
                $tableau_comparaison[$libelle]['DR'] = $total_valeur_dr;
            }
        }

        return isset($tableau_comparaison) ? $tableau_comparaison : null;
    }
}
