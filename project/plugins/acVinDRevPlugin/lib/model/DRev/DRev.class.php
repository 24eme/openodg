<?php

/**
 * Model for DRev
 *
 */
class DRev extends BaseDRev implements InterfaceProduitsDocument, InterfaceVersionDocument, InterfaceDeclarantDocument, InterfaceDeclaration, InterfaceMouvementFacturesDocument, InterfacePieceDocument, InterfaceMouvementLotsDocument, InterfaceArchivageDocument {

    const DEFAULT_KEY = 'DEFAUT';

    protected $declarant_document = null;
    protected $mouvement_document = null;
    protected $version_document = null;
    protected $piece_document = null;
    protected $csv_douanier = null;
    protected $document_douanier_type = null;
    protected $archivage_document = null;
    protected $etablissement = null;

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
        $this->archivage_document = new ArchivageDocument($this);
        $this->csv_douanier = null;
    }

    public function constructId() {
        $id = 'DREV-' . $this->identifiant . '-' . $this->periode;
        if($this->version) {
            $id .= "-".$this->version;
        }
        $this->set('_id', $id);
    }

    public function getConfiguration() {
        return ConfigurationClient::getInstance()->getConfiguration($this->getPeriode().'-10-01');
    }

    public function getPeriode() {
        return substr($this->campagne, 0, 4);
    }

    public function getProduits($region = null, $with_details = true) {
        if (!$this->exist('declaration') || !count($this->get('declaration'))) {
            $this->updateDeclaration();
        }
        return $this->declaration->getProduits($region, $with_details);
    }

    public function getRegions()
    {
        $regions = [];
        foreach ($this->getProduits(null, false) as $hash => $p) {
            $regions[] = $p->getRegion();
        }
        $docDouanier = $this->getDocumentDouanier();
        if ($docDouanier) {
            foreach ($docDouanier->getProduits() as $hash => $p) {
                $regions[] = RegionConfiguration::getInstance()->getOdgRegion($hash);
            }
        }

        return array_values(array_unique($regions));
    }

    public function getProduitsWithoutLots($region = null) {

        return $this->declaration->getProduitsWithoutLots($region);
    }

    public function getProduitsVci($region = null) {

        return $this->declaration->getProduitsVci($region);
    }

    public function getProduitsLots($region = null) {

        return $this->declaration->getProduitsLots($region);
    }

    public function summerizeProduitsLotsByCouleur($with_total = 'appellation') {
        $couleurs = array();
        if (!count($this->declaration)  && $this->hasDocumentDouanier()) {
            $this->resetAndImportFromDocumentDouanier();
        }

        $millesime = substr($this->campagne, 0, 4);
        $hab = HabilitationClient::getInstance()->getLastHabilitation($this->identifiant);
        // Parcours dans le noeud declaration
        foreach($this->getProduitsLots() as $h => $p) {
            if ($hab) {
                foreach ($hab->declaration as $decla => $infos) {
                    if (strpos($h, $decla) && $infos['activites'][HabilitationClient::ACTIVITE_VINIFICATEUR]['statut'] == HabilitationClient::STATUT_EXTERIEUR) {
                        continue 2;
                    }
                }
            }
            $couleur = $p->getConfig()->getCouleur()->getLibelleCompletDR().' '.$millesime;
            if (!isset($couleurs[$couleur])) {
                $couleurs[$couleur] = array('superficie_totale' => 0, 'superficie_revendiquee' => 0,
                                            'volume_total' => 0, 'volume_sur_place' => 0,
                                            'volume_max' => 0, 'volume_lots' => 0,
                                            'volume_restant' => 0, 'volume_restant_max' => 0, 'nb_lots' => 0,
                                            'nb_lots_degustables' => 0, 'is_precis_sur_place' => true,
                                            'millesime' => $millesime
                                           );
            }
            $couleurs[$couleur]['appellation'] = $p->getConfig()->getAppellation()->getLibelleComplet().' XXX'.$millesime.' Total';
            $couleurs[$couleur]['appellation_couleur'] = str_replace(' Vin de base', '', $p->getConfig()->getAppellation()->getLibelleComplet()).' '.$p->getConfig()->getCouleur()->getLibelleDR().' XXX'.$millesime.' Total';
            $couleurs[$couleur]['volume_total'] += $p->recolte->volume_total;
            if(isset($couleurs[$couleur]['volume_sur_place'])) {
                $couleurs[$couleur]['volume_sur_place'] += ($p->canCalculTheoriticalVolumeRevendiqueIssuRecolte()) ? $p->getTheoriticalVolumeRevendiqueIssuRecole() : $p->recolte->volume_sur_place;
            } elseif($p->hasDonneesRecolte()) {
                unset($couleurs[$couleur]['volume_sur_place']);
            }
            $couleurs[$couleur]['volume_max'] += ($p->canCalculTheoriticalVolumeRevendiqueIssuRecolte()) ? $p->getTheoriticalVolumeRevendiqueIssuRecole() : $p->recolte->volume_sur_place;
            $couleurs[$couleur]['is_precis_sur_place'] = $couleurs[$couleur]['is_precis_sur_place'] && $p->canCalculTheoriticalVolumeRevendiqueIssuRecolte();
            $couleurs[$couleur]['superficie_revendiquee'] += $p->superficie_revendique;
            $couleurs[$couleur]['superficie_totale'] += $p->recolte->superficie_total;
        }

        // Parcours dans les lots
        foreach($this->lots as $lot) {
            if(!$lot->produit_hash) {
                continue;
            }
            $couleur = $lot->getConfig()->getCouleur()->getLibelleCompletDR().' '.$lot->millesime;
            if (!isset($couleurs[$couleur])) {
                $couleurs[$couleur] = array('volume_sur_place' => 0, 'volume_total' => 0,
                                            'superficie_totale' => 0, 'superficie_revendiquee' => 0,
                                            'volume_max' => 0, 'volume_lots' => 0,
                                            'volume_restant' => 0, 'volume_restant_max' => 0, 'nb_lots' => 0,
                                            'nb_lots_degustables' => 0, 'millesime' => $lot->millesime
                                           );
            }
            $couleurs[$couleur]['appellation'] = str_replace(' Vin de base', '', $lot->getConfig()->getAppellation()->getLibelleComplet()).' XXX'.$lot->millesime.' Total';
            $couleurs[$couleur]['appellation_couleur'] = str_replace(' Vin de base', '', $lot->getConfig()->getAppellation()->getLibelleComplet()).' '.$lot->getConfig()->getCouleur()->getLibelleDR().' XXX'.$lot->millesime.' Total';
            if($lot->getProduitRevendique()){
                $couleur = $lot->getProduitRevendique()->getConfig()->getCouleur()->getLibelleCompletDR().' '.$lot->millesime;
            }
            if ($lot->volume) {
                $couleurs[$couleur]['volume_lots'] += $lot->volume;
                $couleurs[$couleur]['nb_lots']++;
                if ($lot->isControle()) {
                    $couleurs[$couleur]['nb_lots_degustables']++;
                }
            }
        }
        $total_appellations = array("XXXTotal global" =>  array(
            'superficie_totale' => 0, 'superficie_revendiquee' => 0,
            'volume_sur_place' => 0, 'volume_total' => 0,
            'volume_max' => 0, 'volume_lots' => 0,
            'volume_restant' => 0, 'volume_restant_max' => 0,
            'nb_lots' => 0, 'nb_lots_degustables' => 0, 'is_precis_sur_place' => true
        ));
        $total_couleurs = array("XXXTotal global" =>  array(
            'superficie_totale' => 0, 'superficie_revendiquee' => 0,
            'volume_sur_place' => 0, 'volume_total' => 0,
            'volume_max' => 0, 'volume_lots' => 0,
            'volume_restant' => 0, 'volume_restant_max' => 0,
            'nb_lots' => 0, 'nb_lots_degustables' => 0, 'is_precis_sur_place' => true
        ));

        foreach($couleurs as $k => $couleur) {
            if (!isset($couleur['volume_sur_place'])) {
                $couleur['volume_sur_place'] = 0;
                $couleur['volume_total'] = 0;
            }
            if (isset($couleur['volume_lots']) && $couleur['millesime'] == $millesime) {
                $couleur['volume_restant'] = $couleur['volume_sur_place'] - $couleur['volume_lots'];
                $couleur['volume_restant_max'] = $couleur['volume_max'] - $couleur['volume_lots'];
                $couleurs[$k]['volume_restant'] = $couleur['volume_restant'];
                $couleurs[$k]['volume_restant_max'] = $couleur['volume_restant_max'];
            }
            if (!$with_total) {
                continue;
            }
            if (!isset($total_appellations[$couleur['appellation']])) {
                $total_appellations[$couleur['appellation']] = array(
                    'superficie_totale' => 0, 'superficie_revendiquee' => 0,
                    'volume_sur_place' => 0, 'volume_total' => 0,
                    'volume_max' => 0, 'volume_lots' => 0,
                    'volume_restant' => 0, 'volume_restant_max' => 0,
                    'nb_lots' => 0, 'nb_lots_degustables' => 0, 'is_precis_sur_place' => true,
                    'millesime' => $couleur['millesime']
                );
            }
            $total_appellations[$couleur['appellation']]['volume_total'] += $couleur['volume_total'];
            $total_appellations[$couleur['appellation']]['volume_sur_place'] += $couleur['volume_sur_place'];
            $total_appellations[$couleur['appellation']]['superficie_totale'] += $couleur['superficie_totale'];
            $total_appellations[$couleur['appellation']]['superficie_revendiquee'] += $couleur['superficie_revendiquee'];
            $total_appellations[$couleur['appellation']]['volume_max'] += $couleur['volume_max'];
            $total_appellations[$couleur['appellation']]['is_precis_sur_place'] &= isset($couleur['is_precis_sur_place']) && $couleur['is_precis_sur_place'];
            $total_appellations[$couleur['appellation']]['volume_lots'] += $couleur['volume_lots'];
            $total_appellations[$couleur['appellation']]['volume_restant'] += $couleur['volume_restant'];
            $total_appellations[$couleur['appellation']]['volume_restant_max'] += $couleur['volume_restant_max'];
            $total_appellations[$couleur['appellation']]['nb_lots'] += $couleur['nb_lots'];
            $total_appellations[$couleur['appellation']]['nb_lots_degustables'] += $couleur['nb_lots_degustables'];
            $total_appellations['XXXTotal global']['volume_total'] += $couleur['volume_total'];
            $total_appellations['XXXTotal global']['volume_sur_place'] += $couleur['volume_sur_place'];
            $total_appellations['XXXTotal global']['superficie_totale'] += $couleur['superficie_totale'];
            $total_appellations['XXXTotal global']['superficie_revendiquee'] += $couleur['superficie_revendiquee'];
            $total_appellations['XXXTotal global']['volume_max'] += $couleur['volume_max'];
            $total_appellations['XXXTotal global']['is_precis_sur_place'] &= isset($couleur['is_precis_sur_place']) && $couleur['is_precis_sur_place'];
            $total_appellations['XXXTotal global']['volume_lots'] += $couleur['volume_lots'];
            $total_appellations['XXXTotal global']['volume_restant'] += $couleur['volume_restant'];
            $total_appellations['XXXTotal global']['volume_restant_max'] += $couleur['volume_restant_max'];
            $total_appellations['XXXTotal global']['nb_lots'] += $couleur['nb_lots'];
            $total_appellations['XXXTotal global']['nb_lots_degustables'] += $couleur['nb_lots_degustables'];

            if (!isset($total_couleurs[$couleur['appellation_couleur']])) {
                $total_couleurs[$couleur['appellation_couleur']] = array(
                    'superficie_totale' => 0, 'superficie_revendiquee' => 0,
                    'volume_sur_place' => 0, 'volume_total' => 0,
                    'volume_max' => 0, 'volume_lots' => 0,
                    'volume_restant' => 0, 'volume_restant_max' => 0,
                    'nb_lots' => 0, 'nb_lots_degustables' => 0, 'is_precis_sur_place' => true,
                    'millesime' => $couleur['millesime']
                );
            }
            $total_couleurs[$couleur['appellation_couleur']]['volume_total'] += $couleur['volume_total'];
            $total_couleurs[$couleur['appellation_couleur']]['volume_sur_place'] += $couleur['volume_sur_place'];
            $total_couleurs[$couleur['appellation_couleur']]['superficie_totale'] += $couleur['superficie_totale'];
            $total_couleurs[$couleur['appellation_couleur']]['superficie_revendiquee'] += $couleur['superficie_revendiquee'];
            $total_couleurs[$couleur['appellation_couleur']]['volume_max'] += $couleur['volume_max'];
            $total_couleurs[$couleur['appellation_couleur']]['is_precis_sur_place'] &= isset($couleur['is_precis_sur_place']) && $couleur['is_precis_sur_place'];
            $total_couleurs[$couleur['appellation_couleur']]['volume_lots'] += $couleur['volume_lots'];
            $total_couleurs[$couleur['appellation_couleur']]['volume_restant'] += $couleur['volume_restant'];
            $total_couleurs[$couleur['appellation_couleur']]['volume_restant_max'] += $couleur['volume_restant_max'];
            $total_couleurs[$couleur['appellation_couleur']]['nb_lots'] += $couleur['nb_lots'];
            $total_couleurs[$couleur['appellation_couleur']]['nb_lots_degustables'] += $couleur['nb_lots_degustables'];
        }
        if (count(array_keys($total_appellations)) < 3) {
            unset($total_appellations['XXXTotal global']);
        }

        if($with_total === 'couleur') {
            $couleurs = array_merge($couleurs, $total_couleurs);
        } elseif ($with_total === 'appellation') {
            $couleurs = array_merge($couleurs, $total_appellations);
        }

        ksort($couleurs);
        $keys = array_keys($couleurs);
        foreach($keys as $k) {
            $couleurs[$k]['libelle'] = str_replace('XXX', '', $k);
        }
        return $couleurs;
    }

    public function getLotsRevendiques() {
        $lots = array();
        foreach ($this->getLots() as $lot) {
            if(!$lot->hasVolumeAndHashProduit()){
                continue;
            }

            $lots[] = $lot;
       }

       return $lots;
    }

    public function getLotByNumArchive($numero_archive){
      foreach ($this->lots as $lot) {
        if($lot->numero_archive == $numero_archive){
          return $lot;
        }
      }
      return null;
    }

    public function getLotsByCouleur($visualisation = true) {
        $couleurs = array();

        foreach($this->getProduitsLots() as $h => $p) {
            $couleurs[$p->getConfig()->getCouleur()->getLibelleComplet()] = array();
        }

        foreach ($this->getLots() as $lot) {
           if($visualisation && !$lot->hasVolumeAndHashProduit()){
             continue;
           }
          $couleur = "vide";
          if($lot->produit_hash){
            $couleur = $lot->getConfigProduit()->getCouleur()->getLibelleComplet();
          }
            if (!isset($couleurs[$couleur])) {
                $couleurs[$couleur] = array();
            }
            $couleurs[$couleur][] = $lot;
        }
        return $couleurs;
    }

    public function getLotsHorsDR(){
      $lotsDR = $this->summerizeProduitsLotsByCouleur();
      $lotsHorsDR = array();
      foreach ($this->getLots() as $key => $lot) {
          if($lot->millesime != $this->getPeriode()) {
            continue;
          }
        if(!isset($lotsDR[$lot->produit_libelle])){
            @$lotsHorsDR[$lot->produit_libelle]['volume_lots'] += $lot->volume;
            @$lotsHorsDR[$lot->produit_libelle]['nb_lots']++;
        }
      }
      return $lotsHorsDR;
    }

    public function getLots(){
        if(!$this->exist('lots') && !ConfigurationClient::getCurrent()->declaration->isRevendicationParLots()) {

            return array();
        }

        if(!$this->exist('lots')) {
            $this->add('lots');
        }
        return $this->_get('lots');
    }

    public function getCurrentLots() {
      $lots = array();
      foreach($this->getLots() as $lot) {
        if ($lot->numero_dossier != $this->numero_archive) {
              continue;
        }
        $lots[] = $lot;
      }
      return $lots;
    }

    public function hasDestinationConditionnement(){
        return $this->hasDestionation("CONDITIONNEMENT");
    }

    public function hasDestionationVrac(){
        return $this->hasDestionation("VRAC");
    }

    protected function hasDestionation($type){
        foreach($this->getCurrentLots() as $lot){
            if(strrpos($lot->destination_type,$type) !== false){
                return true;
            }
        }
        return false;
    }

    public function getLotsByNumeroDossier(){
      $lots = array();
      $i=0;
      foreach($this->_get('lots')->toArray(1,1) as $lot) {
          $index = ($lot->campagne&&$lot->numero_dossier&&$lot->numero_archive)? $lot->numero_dossier.$lot->date : $i++;
          $lots[$index] = $lot;
          $i++;
      }
      ksort($lots);
      $lots = array_values($lots);

      return $lots;
    }

    public function getLotsByUniqueAndDate($chrono = true)
    {
        if (! $this->exist('lots')) {
            return [];
        }

        $lots = array();
        $i=0;
        foreach($this->_get('lots')->toArray(1,1) as $lot) {
            $index = ($lot->campagne&&$lot->numero_dossier&&$lot->numero_archive)? $lot->unique_id.$lot->date : $i++;
            $lots[$index] = $lot;
            $i++;
        }
        ksort($lots);
        $lots = array_values($lots);

        if ($chrono) {
            $lots = array_reverse($lots);
        }

        return $lots;
    }

    public function getConfigProduits() {

        return $this->getConfiguration()->declaration->getProduits();
    }

    public function isLectureSeule() {

        return $this->exist('lecture_seule') && $this->get('lecture_seule');
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
        return ($this->getDR());
    }

    public function getDR($periode = null) {

        return $this->getDocumentDouanier();
    }

    private $cache_document_douaniers = null;
    public function getDocumentsDouaniers($ext = null, $hydrate = acCouchdbClient::HYDRATE_DOCUMENT) {
        if (!$this->cache_document_douaniers) {
            $this->cache_document_douaniers = array();
        }
        if (!isset($this->cache_document_douaniers[$ext])) {
            $this->cache_document_douaniers[$ext] = DouaneClient::getInstance()->getDocumentsDouaniers($this->getEtablissementObject(), $this->periode, $ext);
        }
        return $this->cache_document_douaniers[$ext];
    }

    public function getDocumentDouanierOlderThanMe($hydrate = acCouchdbClient::HYDRATE_DOCUMENT) {
        $doc = $this->getDocumentDouanier($hydrate);
        if ( !DRevConfiguration::getInstance()->isModificativeEnabled() || ($doc->date_import <= substr($this->validation_odg, 0, 10)) ) {
            return $doc;
        }
        return null;
    }

    private $cache_document_douanier = null;
    public function getDocumentDouanier($hydrate = acCouchdbClient::HYDRATE_DOCUMENT) {
        if (!$this->cache_document_douanier) {
            $this->cache_document_douanier = DouaneClient::getInstance()->getDocumentDouanierEtablissement(null, $this->periode, $this->getEtablissementObject(), $hydrate);
        }
        return $this->cache_document_douanier;
    }

    public function getDocumentDouanierFile($ext) {
        return DouaneClient::getInstance()->getDocumentDouanierEtablissement($ext, $this->periode, $this->getEtablissementObject());
    }

    public function hasDocumentDouanierForFacturation() {
        return ($this->getDocumentDouanierOlderThanMe());
    }

    public function hasDocumentDouanier() {
        $a = $this->getDocumentsDouaniers();
        if (!$a) {
            return false;
        }
        return count($a);
    }

    public function getDocumentDouanierType() {
        if(!is_null($this->document_douanier_type)) {
            return $this->document_douanier_type;
        }

        $famille = $this->declarant->famille;

        if($famille == 'PRODUCTEUR') {
            $famille = EtablissementFamilles::FAMILLE_PRODUCTEUR;
        }

        if($famille == EtablissementFamilles::FAMILLE_PRODUCTEUR || $famille == EtablissementFamilles::FAMILLE_PRODUCTEUR_VINIFICATEUR) {

            return DRCsvFile::CSV_TYPE_DR;
        }

        if($famille == EtablissementFamilles::FAMILLE_COOPERATIVE) {

            return SV11CsvFile::CSV_TYPE_SV11;
        }
        if(preg_match('/^'.EtablissementFamilles::FAMILLE_NEGOCIANT.'/', $famille)) {

            return SV12CsvFile::CSV_TYPE_SV12;
        }

        $document = $this->getDocumentDouanier(acCouchdbClient::HYDRATE_JSON);

        $this->document_douanier_type = ($document) ? $document->type : null;

        return $this->document_douanier_type;
    }

    public function getDocumentDouanierClient()
    {
    	$type = $this->getDocumentDouanierType();
    	if ($type == DRCsvFile::CSV_TYPE_DR) {
    		return DRClient::getInstance();
    	}
    	if ($type == SV11CsvFile::CSV_TYPE_SV11) {
    		return SV11Client::getInstance();
    	}
    	if ($type == SV12CsvFile::CSV_TYPE_SV12) {
    		return SV12Client::getInstance();
    	}
    	return null;
    }

    public function getDocumentDouanierTypeLibelle() {

        if(!$this->getDocumentDouanierType()) {

            return "Données de la récolte";
        }

        if($this->getDocumentDouanierType() == DRCsvFile::CSV_TYPE_DR) {

            return "Déclaration de récolte";
        }

        return $this->getDocumentDouanierType();
    }

    public function initDoc($identifiant, $periode) {
        $this->identifiant = $identifiant;
        $this->campagne = ConfigurationClient::getInstance()->buildCampagneFromYearOrCampagne($periode);
        $this->etablissement = $this->getEtablissementObject();
        $this->constructId();
    }

    public function getCSV() {
        $csv = new DRCsvFile($this->getAttachmentUri('DR.csv'));
        return $csv->getCsv();
    }

    public function getCsvFromDocumentDouanier() {

        if ($this->csv_douanier != null) {
            return $this->csv_douanier;
        }
    	if (!$this->hasDocumentDouanier()) {
    		return null;
    	}

    	$typeDocumentDouanier = $this->getDocumentDouanierType();
    	$csvFiles = $this->getDocumentsDouaniers('csv');

    	if (!count($csvFiles)) {
    		$docDouanier = $this->getDocumentDouanier();
    		if ($docDouanier &&  $docDouanier->exist('donnees') && count($docDouanier->donnees) >= 1) {
    			$className = DeclarationClient::getInstance()->getExportCsvClassName($typeDocumentDouanier);
    			$csvOrigine = new $className($docDouanier, false);
    			$this->csv_douanier = $csvOrigine->getCsv();
    		}
            return $this->csv_douanier;
    	}

        $csvContent = '';
        foreach($csvFiles as $a_csv_file) {
    	    $csvOrigine = DouaneImportCsvFile::getNewInstanceFromType($typeDocumentDouanier, $a_csv_file);
            if ($csvOrigine) {
    	        $csvContent .= $csvOrigine->convert();
            }
        }

    	if (!$csvContent) {
    		return null;
    	}
    	$path = sfConfig::get('sf_cache_dir').'/dr/';
    	$filename = $csvOrigine->getCsvType().'-'.$this->identifiant.'-'.$this->periode.'.csv';
    	if (!is_dir($path)) {
            umask(0);
    		if (!mkdir($path, 02775)) {
    			throw new sfException('cannot create '.$path);
    		}
            chmod($path, 02775);
    	}
    	file_put_contents($path.$filename, $csvContent);
    	$csv = DouaneCsvFile::getNewInstanceFromType($csvOrigine->getCsvType(), $path.$filename);
        $this->csv_douanier = $csv->getCsv();

    	return $this->csv_douanier;
    }

    public function getFictiveFromDocumentDouanier() {
    	$drev = clone $this;
      $drev->validation = null;
      $drev->validation_odg = null;
        $drev->remove('declaration');
    	$drev->add('declaration');
        $drev->resetAndImportFromDocumentDouanier();
        $drev->add('_rev', "FICTIVE");
    	return $drev;
    }

    public function updateDeclaration() {
        $this->resetAndImportFromDocumentDouanier();
        foreach($this->getProduitsLots() as $produit) {
            $produit->superficie_revendique = 0;
            $produit->volume_revendique_total = 0;
        }
        foreach ($this->getLots() as $lot) {
            $produit = $lot->getProduitRevendique();
            $produit->superficie_revendique = $produit->recolte->superficie_total;
            $produit->volume_revendique_total += $lot->volume;
        }
    }

    public function getBailleurs($cave_particuliere_only = false) {
    	$csv = $this->getCsvFromDocumentDouanier();
      if (!$csv) {
        return array();
      }

        return DouaneProduction::getBailleursFromCsv($this->getEtablissementObject(), $csv, $this->getConfiguration(), $cave_particuliere_only);
    }

    public function resetAndImportFromDocumentDouanier() {
      $this->declarant->famille = $this->getEtablissementObject()->famille;

      if (count($this->getProduitsWithoutLots()) > 0 && $this->declaration->getTotalVolumeRevendique() > 0)  {
          throw new sfException('Volume déjà déclaré');
      }

      if(count($this->getProduitsWithoutLots()) > 0 && $this->isValidee()) {
          throw new sfException('Document validé');
      }

      $this->remove('declaration');
      $this->add('declaration');

      $csv = $this->getCsvFromDocumentDouanier();
      if (!$csv) {
      	return false;
      }

      $this->importCSVDouane($csv);

      return true;
    }

    public function importCSVDouane($csv) {
      if(count($this->declaration) > 0) {
          return;
      }

    	$todelete = array();
        $bailleurs = array();

        $preserve = false;


        $has_bio_in_dr = false;
        $has_hve_in_dr = false;

        $has_coop_l8 = false;
        $has_mout_l7 = false;

        if (DRevConfiguration::getInstance()->hasDenominationAuto()) {
            $labelsDefault = array_fill_keys($this->getDenominationAuto(), true);
            foreach($csv as $k => $line) {
                $labelsDouane = array();
                if(isset($line[DRCsvFile::CSV_LABEL_CALCULEE])) {
                    $labelsDouane = explode("|", $line[DRCsvFile::CSV_LABEL_CALCULEE]);
                }
                if (in_array(DRevClient::DENOMINATION_BIO, $labelsDouane)) {
                    unset($labelsDefault[DRevClient::DENOMINATION_BIO]);
                    continue;
                }
                if (DRevConfiguration::getInstance()->hasDenominationBiodynamie() && in_array(DRevClient::DENOMINATION_BIODYNAMIE, $labelsDouane)) {
                    unset($labelsDefault[DRevClient::DENOMINATION_BIODYNAMIE]);
                    continue;
                }
                if (in_array(DRevClient::DENOMINATION_HVE, $labelsDouane)) {
                    unset($labelsDefault[DRevClient::DENOMINATION_HVE]);
                    continue;
                }
            }
        }
        $has_bailleurs_or_multiple = 0;
        $first_cvi = $csv[0][DRCsvFile::CSV_RECOLTANT_CVI];
        foreach($csv as $k => $line) {
            if ($line[DRCsvFile::CSV_BAILLEUR_PPM]) {
                $has_bailleurs_or_multiple = true;
                break;
            }
            if ($first_cvi != $line[DRCsvFile::CSV_RECOLTANT_CVI]) {
                $has_bailleurs_or_multiple = true;
                break;
            }
        }
        $cvi = $this->declarant->cvi;
        $ppm = $this->declarant->ppm;
        $known_produit = array();
        foreach($csv as $k => $line) {
            $is_bailleur = false;

            $produitConfig = null;
            $produitConfigAlt = null;

            if($line[DouaneCsvFile::CSV_TYPE] == DRCsvFile::CSV_TYPE_DR && $line[DRCsvFile::CSV_LIGNE_CODE] == DRCsvFile::CSV_LIGNE_CODE_ACHAT_TOLERANCE) {
                $this->add('achat_tolerance', 1);
                continue;
            }

            if (!isset($known_produit[$line[DRCsvFile::CSV_PRODUIT_INAO]])) {
                $produitConfig = $this->getConfiguration()->findProductByCodeDouane($line[DRCsvFile::CSV_PRODUIT_INAO]);
                if(!$produitConfig) {
                    if (preg_match('/([a-zA-Z0-9]{5,6}) ([0-9]{1,2})/', $line[DRCsvFile::CSV_PRODUIT_INAO], $m)) {
                        $produitConfig = $this->getConfiguration()->findProductByCodeDouane($m[1]);
                    }
                }
                $known_produit[$line[DRCsvFile::CSV_PRODUIT_INAO]] = $produitConfig;
            }else{
                $produitConfig = $known_produit[$line[DRCsvFile::CSV_PRODUIT_INAO]];
            }

            if (!$produitConfig) {
            	continue;
            }
            if (!$produitConfig->isActif()) {
            	continue;
            }

            if($line[DRCsvFile::CSV_PRODUIT_COMPLEMENT]) {
                if (!isset($known_produit[$produitConfig->getLibelleComplet()." ". $line[DRCsvFile::CSV_PRODUIT_COMPLEMENT]])) {
                    $produitConfigAlt = $this->getConfiguration()->identifyProductByLibelle($produitConfig->getLibelleComplet()." ". $line[DRCsvFile::CSV_PRODUIT_COMPLEMENT]);
                    $known_produit[$produitConfig->getLibelleComplet()." ". $line[DRCsvFile::CSV_PRODUIT_COMPLEMENT]] = $produitConfigAlt;
                }else{
                    $produitConfigAlt = $known_produit[$produitConfig->getLibelleComplet()." ". $line[DRCsvFile::CSV_PRODUIT_COMPLEMENT]];
                }
            }

            if(isset($produitConfigAlt) && $produitConfigAlt && $produitConfigAlt->isActif()) {
                $produitConfig = $produitConfigAlt;
                $line[DRCsvFile::CSV_PRODUIT_COMPLEMENT] = null;
            }

            $labelsDouane = array();
            if(isset($line[DRCsvFile::CSV_LABEL_CALCULEE]) && $line[DRCsvFile::CSV_LABEL_CALCULEE]) {
                $labelsDouane = explode("|", $line[DRCsvFile::CSV_LABEL_CALCULEE]);
            }

            $complement = null;
            if (DRevConfiguration::getInstance()->hasDenominationAuto() && (in_array(DRevClient::DENOMINATION_BIO, $labelsDouane) || (!DRevConfiguration::getInstance()->hasDenominationBiodynamie() && in_array(DRevClient::DENOMINATION_BIODYNAMIE, $labelsDouane)))) {
                $complement = DRevClient::DENOMINATION_BIO_LIBELLE_AUTO;
            } elseif (DRevConfiguration::getInstance()->hasDenominationAuto() && DRevConfiguration::getInstance()->hasDenominationBiodynamie() &&  in_array(DRevClient::DENOMINATION_BIODYNAMIE, $labelsDouane)) {
                $complement = DRevClient::DENOMINATION_BIODYNAMIE_LIBELLE_AUTO;
            } elseif (DRevConfiguration::getInstance()->hasDenominationAuto() && in_array(DRevClient::DENOMINATION_HVE, $labelsDouane)) {
                $complement = DRevClient::DENOMINATION_HVE_LIBELLE_AUTO;
            } elseif (DRevConfiguration::getInstance()->hasDenominationAuto() && count($labelsDefault) == 1 && array_key_first($labelsDefault) != DRevClient::DENOMINATION_CONVENTIONNEL) {
                $complement = DRevClient::getDenominationsAuto()[array_key_first($labelsDefault)];
            } elseif (DRevConfiguration::getInstance()->hasImportDRWithMentionsComplementaire() && $line[DRCsvFile::CSV_PRODUIT_COMPLEMENT]) {
                $complement = $line[DRCsvFile::CSV_PRODUIT_COMPLEMENT];
            }

            if($line[DouaneCsvFile::CSV_TYPE] == DRCsvFile::CSV_TYPE_DR && trim($line[DRCsvFile::CSV_BAILLEUR_PPM])) {
                $is_bailleur = true;
                if($complement) {
                    $complement .= " - ";
                }
                $complement .= $line[DRCsvFile::CSV_RECOLTANT_LIBELLE];
            }
            $produit = $this->addProduit($produitConfig->getHash(), $complement, $line[DRCsvFile::CSV_COLONNE_ID]);

            if($is_bailleur) {
                $bailleurs[$produit->getHash()] = $produit->getHash();
            }

            if ($is_bailleur && (!$has_bailleurs_or_multiple || !$ppm || $ppm != trim($line[DRCsvFile::CSV_BAILLEUR_PPM]))) {
                continue;
            }
            if (!$is_bailleur && $has_bailleurs_or_multiple && (!$cvi || $cvi != trim($line[DRCsvFile::CSV_RECOLTANT_CVI]))) {
                continue;
            }

            $produitRecolte = $produit->recolte;

            if($line[DouaneCsvFile::CSV_TYPE] == DRCsvFile::CSV_TYPE_DR && $line[DRCsvFile::CSV_LIGNE_CODE] == DRCsvFile::CSV_LIGNE_CODE_RECOLTE_L5) {
            	$produitRecolte->volume_total += VarManipulator::floatize($line[DRCsvFile::CSV_VALEUR]);
            }
            if ($line[DouaneCsvFile::CSV_TYPE] == DRCsvFile::CSV_TYPE_DR && $line[DRCsvFile::CSV_LIGNE_CODE] == DRCsvFile::CSV_LIGNE_CODE_USAGESIND_L16) {
            	$produitRecolte->usages_industriels_total += VarManipulator::floatize($line[DRCsvFile::CSV_VALEUR]);
                if (!$has_coop_l8) {
                    if (!$has_mout_l7) {
                        $produitRecolte->usages_industriels_sur_place += VarManipulator::floatize($line[DRCsvFile::CSV_VALEUR]);
                    }else{
                        $produitRecolte->usages_industriels_sur_place = $produitRecolte->volume_sur_place - $produitRecolte->volume_sur_place_revendique;
                    }
                }
            }
            if ($line[DouaneCsvFile::CSV_TYPE] == DRCsvFile::CSV_TYPE_DR && $line[DRCsvFile::CSV_LIGNE_CODE] == DRCsvFile::CSV_LIGNE_CODE_SUPERFICIE_L4) {
                $produitRecolte->superficie_total += round(VarManipulator::floatize($line[DRCsvFile::CSV_VALEUR]), 4);
                $has_coop_l8 = false;
            }
            if ($line[DouaneCsvFile::CSV_TYPE] == DRCsvFile::CSV_TYPE_DR && $line[DRCsvFile::CSV_LIGNE_CODE] == DRCsvFile::CSV_LIGNE_CODE_COOPERATIVE_L8 && $line[DRCsvFile::CSV_VALEUR])  {
                $has_coop_l8 = true;
            }
            if ($line[DouaneCsvFile::CSV_TYPE] == DRCsvFile::CSV_TYPE_DR && $line[DRCsvFile::CSV_LIGNE_CODE] == DRCsvFile::CSV_LIGNE_CODE_ACHETEUR_MOUTS_L7 && $line[DRCsvFile::CSV_VALEUR])  {
                $has_mout_l7 = true;
            }
            if ($line[DouaneCsvFile::CSV_TYPE] == DRCsvFile::CSV_TYPE_DR && $line[DRCsvFile::CSV_LIGNE_CODE] == DRCsvFile::CSV_LIGNE_CODE_VOLUME_L9)  {
            	$produitRecolte->volume_sur_place += VarManipulator::floatize($line[DRCsvFile::CSV_VALEUR]);
            }
            if ($line[DouaneCsvFile::CSV_TYPE] == DRCsvFile::CSV_TYPE_DR && $line[DRCsvFile::CSV_LIGNE_CODE] == DRCsvFile::CSV_LIGNE_CODE_RECOLTE_NETTE_L15) {
            	$produitRecolte->recolte_nette += VarManipulator::floatize($line[DRCsvFile::CSV_VALEUR]);
                if (!$has_coop_l8){
                    $produitRecolte->volume_sur_place_revendique += VarManipulator::floatize($line[DRCsvFile::CSV_VALEUR]);
                }
            }
            if ($line[DouaneCsvFile::CSV_TYPE] == DRCsvFile::CSV_TYPE_DR && $line[DRCsvFile::CSV_LIGNE_CODE] == DRCsvFile::CSV_LIGNE_CODE_VCI_L19) {
                if (!$has_coop_l8){
                    $produitRecolte->vci_constitue += VarManipulator::floatize($line[DRCsvFile::CSV_VALEUR]);
                    $produit->vci->constitue = $produitRecolte->vci_constitue;
                }
            }
            if ($line[DouaneCsvFile::CSV_TYPE] == SV11CsvFile::CSV_TYPE_SV11 && $line[SV11CsvFile::CSV_LIGNE_CODE] == SV11CsvFile::CSV_LIGNE_CODE_VOLUME_VCI) {
                $produitRecolte->vci_constitue += VarManipulator::floatize($line[SV11CsvFile::CSV_VALEUR]);
                $produit->vci->constitue = $produitRecolte->vci_constitue;
            }

            if ($line[DouaneCsvFile::CSV_TYPE] == DRCsvFile::CSV_TYPE_DR && $line[DRCsvFile::CSV_LIGNE_CODE] == DRCsvFile::CSV_LIGNE_CODE_VSI_L18) {
                if (!$has_coop_l8){
                    if(!$produitRecolte->exist('vsi')) {
                        $produitRecolte->add('vsi');
                    }
                    $produitRecolte->vsi += VarManipulator::floatize($line[DRCsvFile::CSV_VALEUR]);
                }
            }
            if ($line[DouaneCsvFile::CSV_TYPE] == SV11CsvFile::CSV_TYPE_SV11 && $line[SV11CsvFile::CSV_LIGNE_CODE] == SV11CsvFile::CSV_LIGNE_CODE_VOLUME_VSI) {
                if(!$produitRecolte->exist('vsi')) {
                    $produitRecolte->add('vsi');
                }
                $produitRecolte->vsi += VarManipulator::floatize($line[DRCsvFile::CSV_VALEUR]);
            }

            if ($line[DouaneCsvFile::CSV_TYPE] == SV12CsvFile::CSV_TYPE_SV12 && $line[SV12CsvFile::CSV_LIGNE_CODE] == SV12CsvFile::CSV_LIGNE_CODE_SUPERFICIE) {
                $produitRecolte->superficie_total += round(VarManipulator::floatize($line[SV12CsvFile::CSV_VALEUR]), 4);
            }
            if ($line[DouaneCsvFile::CSV_TYPE] == SV12CsvFile::CSV_TYPE_SV12 && $line[SV12CsvFile::CSV_LIGNE_CODE] == SV12CsvFile::CSV_LIGNE_CODE_VOLUME_TOTAL) {
                $produitRecolte->recolte_nette += VarManipulator::floatize($line[SV12CsvFile::CSV_VALEUR]);
                $produitRecolte->volume_total += VarManipulator::floatize($line[SV12CsvFile::CSV_VALEUR]);
                $produitRecolte->volume_sur_place += VarManipulator::floatize($line[SV12CsvFile::CSV_VALEUR]);
            }

            if ($line[DouaneCsvFile::CSV_TYPE] == SV11CsvFile::CSV_TYPE_SV11 && $line[SV11CsvFile::CSV_LIGNE_CODE] == SV11CsvFile::CSV_LIGNE_CODE_SUPERFICIE) {
                $produitRecolte->superficie_total += round(VarManipulator::floatize($line[SV11CsvFile::CSV_VALEUR]), 4);
            }

            if ($line[DouaneCsvFile::CSV_TYPE] == SV11CsvFile::CSV_TYPE_SV11 && $line[SV11CsvFile::CSV_LIGNE_CODE] == SV11CsvFile::CSV_LIGNE_CODE_VOLUME_APTE) {
                $produitRecolte->recolte_nette += VarManipulator::floatize($line[SV11CsvFile::CSV_VALEUR]);
                $produitRecolte->volume_total += VarManipulator::floatize($line[SV11CsvFile::CSV_VALEUR]);
                $produitRecolte->volume_sur_place += VarManipulator::floatize($line[SV11CsvFile::CSV_VALEUR]);
            }
        }
        //Si on n'a pas de volume sur place
        foreach ($this->declaration->getProduits() as $hash => $p) {
            if (!$p->recolte->volume_sur_place && !$p->superficie_revendique && !$p->volume_revendique_total && !$p->hasVci()) {
    		   $todelete[$hash] = $hash;
               continue;
        	}
        }

        foreach ($todelete as $del) {
            $this->remove($del);
        }
        $todelete = array();

        //Supprime les colonnes pour ne proposer qu'un aggréga par produit
        $my_produits = $this->declaration->getProduits();
        foreach ($my_produits as $hash => $p) {
            $hash_produit = $p->getParent()->getHash();
            $produit = $this->addProduit($hash_produit, $p->denomination_complementaire);
            $produitRecolte = $produit->add("recolte");

            if ($p->recolte->volume_sur_place) {
                $produitRecolte->volume_sur_place += $p->recolte->volume_sur_place;
            }
            if ($p->recolte->volume_sur_place_revendique) {
                $produitRecolte->volume_sur_place_revendique += $p->recolte->volume_sur_place_revendique;
            }
            if ($p->recolte->usages_industriels_sur_place) {
                $produitRecolte->usages_industriels_sur_place += $p->recolte->usages_industriels_sur_place;
            }
            if ($p->recolte->usages_industriels_total) {
                $produitRecolte->usages_industriels_total += $p->recolte->usages_industriels_total;
            }
            if ($p->recolte->volume_total) {
                $produitRecolte->volume_total += $p->recolte->volume_total;
            }
            if ($p->recolte->superficie_total) {
                $produitRecolte->superficie_total += round($p->recolte->superficie_total, 4);
            }
            if ($p->recolte->recolte_nette) {
                $produitRecolte->recolte_nette += $p->recolte->recolte_nette;
            }
            if ($p->recolte->vci_constitue) {
                $produitRecolte->vci_constitue += $p->recolte->vci_constitue;
            }
            if ($produitRecolte->vci_constitue) {
                $produit->vci->constitue = $produitRecolte->vci_constitue;
            }

            if ($p->recolte->exist('vsi') && $p->recolte->vsi) {
                $produitRecolte->add('vsi');
                $produitRecolte->vsi += $p->recolte->vsi;
            }

            $todelete[$hash] = $hash;
        }

        foreach ($todelete as $del) {
            $this->remove($del);
        }

        foreach ($this->declaration->getProduits() as $hash => $p) {
            if ($p->recolte->volume_total && $p->recolte->volume_sur_place && round($p->recolte->volume_total, 4) == round($p->recolte->volume_sur_place, 4) && !in_array($p->getHash(), $bailleurs)) {
                $p->superficie_revendique = $p->recolte->superficie_total;
            }
        }

        if (DRevConfiguration::getInstance()->hasDenominationAuto() && count($labelsDefault) > 1) {
            foreach ($this->declaration->getProduits() as $hash => $p) {
                if($p->denomination_complementaire) {
                    continue;
                }
                foreach(array_keys($labelsDefault) as $label) {
                    if($label == DRevClient::DENOMINATION_CONVENTIONNEL) {
                        continue;
                    }
                    $this->addProduit($p->getParent()->getHash(), DRevClient::getDenominationsAuto()[$label]);
                }
                $p->superficie_revendique = null;
            }
        }

        $this->updateVCIFromPrecedente();
    }

    public function updateVCIFromPrecedente()
    {
    	if ($precedente = DRevClient::getInstance()->findMasterByIdentifiantAndPeriode($this->identifiant, $this->periode - 1)) {
        foreach($precedente->getProduitsVci() as $produit) {
          if ($produit->vci->stock_final) {
            $this->cloneProduit($produit);
          }
        }
    	}
    }

    public function canHaveVCI() {
        foreach ($this->getProduits() as $produit) {
            if ($produit->getConfig()->canHaveVCI()) {
                return true;
            }
        }
        return false;
    }

    public function updateFromDRev($drev) {
        foreach ($drev->getProduits() as $produit) {
        	if (!$produit->getConfig()->isActif()) {
        		continue;
        	}
          $p = $this->addProduit($produit->getProduitHash(), $produit->denomination_complementaire);
        }
    }

    public static function buildDetailKey($denominationComplementaire = null, $hidden_denom = null) {
        $detailKey = self::DEFAULT_KEY;
        if(!$denominationComplementaire) {
            $denominationComplementaire = '';
        }
        if(!$hidden_denom) {
            $hidden_denom = '';
        }
        if($denominationComplementaire || $hidden_denom){
            $detailKey = substr(hash("sha1", KeyInflector::slugify(trim($denominationComplementaire).trim($hidden_denom))), 0, 7);
        }

        return $detailKey;
    }

    public function addProduit($hash, $denominationComplementaire = null, $hidden_denom = null) {
        $detailKey = self::buildDetailKey($denominationComplementaire, $hidden_denom);

        $hashToAdd = preg_replace("|/declaration/|", '', $hash);
        $exist = $this->exist('declaration/'.$hashToAdd);
        $produit = $this->add('declaration')->add($hashToAdd)->add($detailKey);
        $produit->denomination_complementaire = null;
        if($denominationComplementaire) {
            $produit->denomination_complementaire = $denominationComplementaire;
        }
        $produit->getLibelle();

        if(!$exist) {
            $this->declaration->reorderByConf();
        }

        return $this->get($produit->getHash());
    }

    public function cloneProduit($produit) {
      $pclone = $this->declaration->add(preg_replace('/\/declaration\//', '', $produit->getParent()->getHash()))
        ->add($produit->getKey());
      $pclone->denomination_complementaire = $produit->denomination_complementaire;
      $pclone->vci->stock_precedent = $produit->vci->stock_final;
      return $pclone;
    }

    public function cleanDoc() {
        $this->declaration->cleanNode();
        $this->cleanLots();
        $this->clearMouvementsLots();
        $this->clearMouvementsFactures();
    }

    public function cleanLots() {
        if(!$this->exist('lots')) {
            return;
        }
        $lotsToKeep = array();

        foreach($this->lots as $keyLot => $lot) {
            if(!$lot->isCleanable()) {
                $lotsToKeep[] = $lot;
            }
        }
         $this->remove('lots');
         $this->add('lots', $lotsToKeep);
    }

    public function addLot($imported = false) {
        if(!$imported && $this->isValidee()) {
            return null;
        }
        $lot = $this->add('lots')->add();
        $lot->id_document = $this->_id;
        $lot->campagne = $this->getCampagne();
        $lot->declarant_identifiant = $this->identifiant;
        $lot->declarant_nom = $this->declarant->raison_sociale;
        $lot->adresse_logement = $this->constructAdresseLogement();
        $lot->affectable = true;
        $lot->initDefault();
        return $lot;
    }

    public function storeDeclarant() {
        $this->declarant_document->storeDeclarant();

        if($this->getEtablissementObject()->famille) {
            $this->declarant->famille = $this->getEtablissementObject()->famille;
        }
    }

    public function storeEtape($etape) {
        $etapeOriginal = ($this->exist('etape')) ? $this->etape : null;

        $this->add('etape', $etape);

        return $etapeOriginal != $this->etape;
    }

    public function getDateValidationFormat($format = 'Y-m-d') {
        if (!$this->validation) {
            return "";
        }
        return date ($format, strtotime($this->validation));
    }

    public function validate($date = null) {
        if(is_null($date)) {
            $date = date('c');
        }

        $this->cleanDoc();
        $this->validation = $date;

        foreach($this->lots as $lot) {
            if($lot->hasBeenEdited()) {
                continue;
            }
            if($lot->specificite == Lot::SPECIFICITE_UNDEFINED) {
                $lot->specificite = null;
            }
            if(!$lot->date) {
                $lot->date = $date;
            }
            if (!$lot->produit_hash) {
                throw new sfException("le lot ".$lot->unique_id." n'a pas de hash produit");
            }
        }
        $this->setStatutOdgByRegion(DRevClient::STATUT_SIGNE);
    }

    public function delete() {
        parent::delete();
        $this->saveDocumentsDependants();
    }

    public function devalidate() {
        $this->getDateDepot();

        $this->validation = null;
        $this->validation_odg = null;
        if($this->exist('etape')) {
            $this->etape = null;
        }
        if($this->exist("envoi_oi")){
         $this->envoi_oi = null;
        }
        if(DrevConfiguration::getInstance()->hasValidationOdgRegion()) {
            foreach($this->getRegions() as $region) {
                foreach ($this->getProduits($region) as $hash => $produit) {
                    if ($produit->exist('validation_odg')) {
                        $produit->validation_odg = null;
                    }
                }
            }
        }

        $this->setStatutOdgByRegion(DRevClient::STATUT_BROUILLON);
    }

    public function validateOdg($date = null, $region = null) {
        if(is_null($date)) {
            $date = date('c');
        }

        if(!$region && RegionConfiguration::getInstance()->hasOdgProduits() && DrevConfiguration::getInstance()->hasValidationOdgRegion()) {
            throw new sfException("La validation nécessite une région");
        }

        if(RegionConfiguration::getInstance()->hasOdgProduits() && $region){
            $this->setStatutOdgByRegion(DRevClient::STATUT_VALIDATION_ODG, $region);
            return $this->validateOdgByRegion($date, $region);
        }

        $this->validation_odg = $date;
        $this->setStatutOdgByRegion(DRevClient::STATUT_VALIDATION_ODG, $region);

        if(!$this->numero_archive) {
            $this->save();
        }

    }

    public function setStatutOdgByRegion($statut, $region = null) {
        if(DrevConfiguration::getInstance()->hasValidationOdgRegion()) {
            if($region) {
                foreach ($this->getProduits($region) as $hash => $produit) {
                    $produit->setStatutOdg($statut);
                }
            } else {
                foreach (RegionConfiguration::getInstance()->getOdgRegions() as $region) {
                    $this->setStatutOdgByRegion($statut, $region);
                }
            }
        }else{
            foreach ($this->getProduits($region) as $hash => $produit) {
                $produit->setStatutOdg($statut);
            }
        }
        $allStatut = true;
        foreach ($this->declaration->getProduits() as $key => $produit) {
            if($produit->getStatutOdg() == $statut){
               continue;
            }
            $allStatut = false;
            break;
        }
        if(!$allStatut) {
            return;
        }
        if (!$this->exist('statut_odg')) {
            return $this->add('statut_odg', $statut);
        }
        return $this->_set('statut_odg', $statut);
    }

    public function isMiseEnAttenteOdg() {
        return ($this->getStatutOdg() == DRevClient::STATUT_EN_ATTENTE);
    }

    public function getStatutOdg() {
        if (!$this->exist('statut_odg')) {
            return null;
        }
        return $this->_get('statut_odg');
    }

    protected function validateOdgByRegion($date = null, $region = null) {
        if($region) {
            foreach ($this->getProduits($region) as $hash => $produit) {
                $produit->validateOdg($date);
            }
        } else {
            foreach (RegionConfiguration::getInstance()->getOdgRegions() as $region) {
                $this->validateOdg($date, $region);
            }
        }

        $no_validate = false;
        foreach ($this->declaration->getProduits() as $key => $produit) {
            if($produit->isValidateOdg() === false){
                $no_validate = true;
                break;
            }
        }

        if($this->isModificative()){
            $this->getMother()->validateOdgByRegion($date, $region);
            $this->getMother()->save();
        }

        if(!$this->isFactures()){
            $this->clearMouvementsFactures();
            $this->generateMouvementsFactures();
        }

        if($no_validate) {

            return;
        }

        $this->validation_odg = $date;
    }

    public function isValidateOdgByRegion($region){
      if (!$region) {
          return false;
      }

      if(!count($this->getProduits($region))) {
           return false;
      }

      foreach ($this->getProduits($region) as $hash => $produit) {
        if(!$produit->isValidateOdg()){
          return false;
        }
      }
      return true;
    }

    public function getValidationOdgDateByRegion($region){
      if(!$region){
        return null;
      }
      foreach ($this->getProduits($region) as $hash => $produit) {
        if($produit->isValidateOdg()){
          return $produit->validation_odg;
        }
      }
      return $this->validation_odg;
    }

    public function getEtablissementObject() {
        if($this->etablissement) {

            return $this->etablissement;
        }

        $this->etablissement = EtablissementClient::getInstance()->findByIdentifiant($this->identifiant);

        return $this->etablissement;
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

    public function hasVSI()
    {
        foreach ($this->declaration->getProduits() as $produit) {
    		if($produit->recolte->exist('vsi') && $produit->recolte->vsi > 0) {

                return true;
            }

            if($produit->exist('volume_revendique_issu_vsi') && $produit->volume_revendique_issu_vsi > 0) {

                return true;
            }
    	}

        return false;
    }

    public function hasVCIConstitue()
    {
        foreach ($this->declaration->getProduits() as $produit) {
    		if($produit->recolte->vci_constitue > 0) {

                return true;
            }
    	}

        return false;
    }

    public function hasVCIRevendique()
    {
        foreach ($this->declaration->getProduits() as $produit) {
    		if($produit->volume_revendique_issu_vci > 0) {

                return true;
            }
    	}

        return false;
    }

    public function isAdresseLogementDifferente() {
        if(!$this->chais->nom && !$this->chais->adresse && !$this->chais->commune && !$this->chais->code_postal) {

            return false;
        }

        return ($this->chais->nom != $this->declarant->nom || $this->chais->adresse != $this->declarant->adresse || $this->chais->commune != $this->declarant->commune || $this->chais->code_postal != $this->declarant->code_postal);
    }

    public function isAllDossiersHaveSameAddress(){
        return (count($this->getLotsByAdresse()) === 1);
    }

    public function updateAddressCurrentLots(){
      foreach($this->getCurrentLots() as $lot) {
        $lot->adresse_logement = $this->constructAdresseLogement();
      }
    }

    public function getLotsByAdresse(){
      $lotsAdresse = array();
      foreach ($this->getLotsByNumeroDossier() as $lot){
        $lotsAdresse[$lot->adresse_logement][] = $lot;
      }
      return $lotsAdresse;
    }

    public function constructAdresseLogement(){
        $completeAdresse = sprintf("%s — %s — %s  %s",$this->declarant->nom,$this->declarant->adresse,$this->declarant->code_postal,$this->declarant->commune);
        $completeAdresse .= $this->declarant->telephone_mobile ? " — ".$this->declarant->telephone_mobile : "";
        $completeAdresse .= $this->declarant->telephone_bureau ? " — ".$this->declarant->telephone_bureau : "";

        if($this->isAdresseLogementDifferente()){
            $completeAdresse = $this->chais->nom ? $this->chais->nom." — " : "";
            $completeAdresse .= $this->chais->adresse ? $this->chais->adresse."  " :"";
            $completeAdresse .= $this->chais->code_postal ? $this->chais->code_postal."  " :  "";
            $completeAdresse .= $this->chais->commune ? $this->chais->commune : "";
            $completeAdresse .= $this->chais->telephone ? " — ".$this->chais->telephone : "";
        }

        return trim($completeAdresse);//trim(preg_replace('/\s+/', ' ', $completeAdresse));
     }

	protected function doSave() {
        $this->piece_document->generatePieces();
        foreach ($this->declaration->getProduits() as $key => $produit) {
            $produit->update();
        }
	}

    public function saveDocumentsDependants() {
        $mother = $this->version_document->getMother(true);

        if(!$mother) {

            return;
        }

        $mother->save(false);
        $docs2save = array();
        foreach($this->getDeletedLots() as $lot) {
            $docs2save[$lot->id_document] = $lot->id_document;
        }
        foreach($docs2save as $id) {
            $drev = DRevClient::getInstance()->find($id);
            if (!$drev) {
                throw new sfException("DREV $id not found");
            }
            $drev->save(false);
        }

        DeclarationClient::getInstance()->clearCache();
    }

    public function save($saveDependants = true) {
        $this->archiver();

        $this->getDateDepot();

        $this->updateAddressCurrentLots();

        $this->generateMouvementsLots();

        if(!$this->isFactures() && $this->isValideeOdg()){
            $this->clearMouvementsFactures();
            $this->generateMouvementsFactures();
        }

        if (RegionConfiguration::getInstance()->hasOdgProduits()) {
            $regions = $this->getRegions();
            if (count($regions)) {
                $this->add('region', implode('|', $regions));
            }
        }

        $saved = parent::save();

        if($saveDependants) {
            $this->saveDocumentsDependants();
        }

        $this->hasVolumeSeuilAndSetIfNecessary();

        return $saved;
    }

    public function archiver() {
        $this->add('type_archive', 'Revendication');
        if (!$this->isArchivageCanBeSet()) {
            return;
        }
        $this->archivage_document->preSave();
        $this->archiverLot();
    }

  /*** ARCHIVAGE ***/

  public function getNumeroArchive() {

      return $this->_get('numero_archive');
  }

  public function isArchivageCanBeSet() {

      return $this->isValidee();
  }

  public function archiverLot() {
      $lots = array();
      if (!$this->numero_archive) {
          throw new sfException("Ne peut archiver les lots sans numero d'archive dans la DRev");
      }
      foreach($this->lots as $lot) {
        if ($lot->numero_archive) {
            continue;
        }
        $lots[] = $lot;
      }
      if(!count($lots)) {
          return;
      }
      $lastNum = ArchivageAllView::getInstance()->getLastNumeroArchiveByTypeAndCampagne(Lot::TYPE_ARCHIVE, $this->archivage_document->getCampagne());
      $num = 0;
      if (preg_match("/^([0-9]+).*/", $lastNum, $m)) {
        $num = $m[1];
      }
      foreach($lots as $lot) {
          $num++;
          $lot->numero_dossier = $this->numero_archive;
          $lot->numero_archive = sprintf("%05d", $num);
      }
      DeclarationClient::getInstance()->clearCache();
  }

  /*** FIN ARCHIVAGE ***/

	public function hasVciDetruit()
	{
		return $this->declaration->hasVciDetruit();
	}

    public function getDateCommission() {
        if(!$this->exist('date_commission')) {

            return null;
        }

        return $this->_get('date_commission');
    }

    public function getDateDepot()
	{
        if($this->validation && (!$this->exist('date_depot') || !$this->_get('date_depot'))) {
            $date = new DateTime($this->validation);
            $this->add('date_depot', $date->format('Y-m-d'));
        }

        if(!$this->exist('date_depot')) {

            return null;
        }

        return $this->_get('date_depot');
    }

	public function getDateValidation($format = 'Y-m-d')
	{
		if ($this->validation) {
			$date = new DateTime($this->validation);
		} else {
			$date = new DateTime($this->getDate());
		}
		return $date->format($format);
	}

    /*
     * Facture
     */
	public function getSurfaceFacturable()
	{
		return $this->declaration->getTotalTotalSuperficie();
	}

	public function getVolumeFacturable(TemplateFactureCotisationCallbackParameters $produitFilter)
	{
		$volume = $this->declaration->getTotalVolumeRevendique($produitFilter);
        foreach($this->getDeletedLots() as $lot) {
            $volume -= $lot->volume;
        }
        return $volume;
	}

	public function getSurfaceVinifieeFacturable()
	{
		return $this->declaration->getTotalSuperficieVinifiee();
	}

    public function getTotalVolumeRevendique()
    {

        return $this->declaration->getTotalVolumeRevendique();
    }

    public function getTotalVolumeRevendiqueVCI()
    {

        return $this->declaration->getTotalVolumeRevendiqueVCI();
    }

    public function getVolumeRevendiqueNumeroDossier(TemplateFactureCotisationCallbackParameters $produitFilter)
    {

        return $this->getVolumeRevendiqueNumeroDossierDiff($produitFilter);
    }

    public function getVolumeRevendiqueNumeroDossierDiff(TemplateFactureCotisationCallbackParameters $produitFilter)
    {
        $lots = [];
        $lotsmodifsvolumes = [];
        $volume_mod = 0;
        foreach ($this->getLots() as $lot) {
            if (DRevClient::getInstance()->matchFilterLot($lot, $produitFilter) === false) {
                continue;
            }
            if ($lot->numero_dossier === $this->numero_archive && $lot->id_document == $this->getDocument()->_id) {
                $original_volume = $lot->getOriginalVolumeIfModifying();
                $lots[] = $lot;
                if ($original_volume !== false) {
                    $lotsmodifsvolumes[] = $lot;
                }
                $volume_mod += $lot->volume - $original_volume;
            }
        }

        $deleted = false;
        foreach($this->getDeletedLots() as $lot) {
            if (DRevClient::getInstance()->matchFilterLot($lot, $produitFilter) === false) {
                continue;
            }
            $volume_mod -= $lot->volume;
            $deleted = true;
        }

        if(!$deleted && count($lotsmodifsvolumes) === 0 && !$this->isFirstNumeroDossier()) {

            return 0;
        }

        return $volume_mod;
    }

    public function isFirstNumeroDossier() {
        $mother = $this->getMother();
        if(!$mother) {
            return true;
        }
        foreach($mother->lots as $lot) {
            if ($lot->numero_dossier === $this->numero_archive) {

                return false;
            }
        }

        return true;
    }

    public function getNumerosDossier()
    {
        if (!$this->exist('lots')) {
            return array();
        }
        return array_unique(array_column($this->lots->toArray(true, false), 'numero_dossier'));
    }

    public function hasVolumeRevendiqueLots(TemplateFactureCotisationCallbackParameters $produitFilter) {

        return $this->getVolumeRevendiqueLots($produitFilter) > 0;
    }

    public function getVolumeRevendiqueLotsWithFilterAppellations(string $appellations){
        $t = new TemplateFactureCotisationCallbackParameters($this, array('appellations' => $appellations));
        return $this->getVolumeRevendiqueLots($t);
    }

    public function getVolumeRevendiqueLots(TemplateFactureCotisationCallbackParameters $produitFilter){
        return $this->getInternalVolumeRevendique($this->getLots(), $produitFilter);
    }

    private function getInternalVolumeRevendique($lots, TemplateFactureCotisationCallbackParameters $produitFilter) {
        $total = 0;
        foreach($lots as $lot) {
            if (DRevClient::getInstance()->matchFilterLot($lot, $produitFilter) === false) {
                continue;
            }

            $total += $lot->volume;
        }
        return $total;
    }

    public function getVolumeVininifieFromDocumentDouanier(TemplateFactureCotisationCallbackParameters $produitFilter) {
        $docDouanier = $this->getDocumentDouanierOlderThanMe();
        if(!$docDouanier) {
            return;
        }
        $type = $docDouanier->type;
        if (!$type) {
            return ;
        }
        if ($type == DRCsvFile::CSV_TYPE_DR) {
            try {
                return $docDouanier->getTotalValeur(DRCsvFile::CSV_LIGNE_CODE_RECOLTE_NETTE_L15, array(DouaneProduction::FAMILLE_CAVE_PARTICULIERE_TOTAL, DouaneProduction::FAMILLE_CAVE_PARTICULIERE_ET_APPORTEUR_NEGOCE), $produitFilter, null, array(DouaneProduction::FAMILLE_CAVE_PARTICULIERE_ET_APPORTEUR_COOP,DouaneProduction::FAMILLE_CAVE_PARTICULIERE_ET_APPORTEUR_COOP_ET_NEGOCE));
            }catch(sfException $e) {
                //Si la L15 ne permet pas d'avoir le vin clair,
                //On prend :
                // - les L15 qui ne sont pas ambigues
                // - les L9 des colonnes qui apportent également en cave coop
                $v = $docDouanier->getTotalValeur(DRCsvFile::CSV_LIGNE_CODE_RECOLTE_NETTE_L15, array(DouaneProduction::FAMILLE_CAVE_PARTICULIERE_TOTAL, DouaneProduction::FAMILLE_CAVE_PARTICULIERE_ET_APPORTEUR_NEGOCE), $produitFilter);
                $v += $docDouanier->getTotalValeur(DRCsvFile::CSV_LIGNE_CODE_RECOLTE_NETTE_L15, array(DouaneProduction::FAMILLE_CAVE_PARTICULIERE_ET_APPORTEUR_COOP,DouaneProduction::FAMILLE_CAVE_PARTICULIERE_ET_APPORTEUR_COOP_ET_NEGOCE), $produitFilter);
                return $v;
            }
        }
        if ($type == SV11CsvFile::CSV_TYPE_SV11) {
            return $docDouanier->getTotalValeur(SV11CsvFile::CSV_LIGNE_CODE_VOLUME_APTE, null, $produitFilter);
        }
        if ($type == SV12CsvFile::CSV_TYPE_SV12) {
            return $docDouanier->getTotalValeur(SV12CsvFile::CSV_LIGNE_CODE_VOLUME_TOTAL, null, $produitFilter);
        }
        throw new sfException("type de document douanier $type n'est pas supporté");
    }

    public function getVolumeIGPSIGFromDR($produitFilter = null) {
        $dr = $this->getDocumentDouanierOlderThanMe();
        if (!$dr || ($dr->type != DRClient::TYPE_MODEL)) {
            return null;
        }
        return $dr->getTotalValeur("15", null, $produitFilter, DouaneProduction::FAMILLE_APPORTEUR_COOP_TOTAL) + $dr->getTotalValeur("14", null, $produitFilter, DouaneProduction::FAMILLE_APPORTEUR_COOP_TOTAL);
    }

    public function getVolumeIGPSIGFromSV11($produitFilter = null) {
        $sv11 = $this->getDocumentDouanierOlderThanMe();
        if (!$sv11 || ($sv11->type != SV11Client::TYPE_MODEL)) {
            return ;
        }
        return $sv11->getTotalValeur("10");
    }

    public function getSuperficieFromDR($produitFilter = null) {
        $docDouanier = $this->getDocumentDouanierOlderThanMe();
        if (!$docDouanier) {
            return ;
        }
        if ($docDouanier->type != DRCsvFile::CSV_TYPE_DR) {
            return ;
        }
        return $docDouanier->getTotalValeur(DRCsvFile::CSV_LIGNE_CODE_SUPERFICIE_L4, null, $produitFilter);
    }

    public function getVolumeVinifiableFromDR($produitFilter = null) {
        $docDouanier = $this->getDocumentDouanierOlderThanMe();
        if (!$docDouanier) {
            return ;
        }
        if ($docDouanier->type != DRCsvFile::CSV_TYPE_DR) {
            return ;
        }
        return $docDouanier->getTotalValeur(DRCsvFile::CSV_LIGNE_CODE_RECOLTE_L5, null, $produitFilter)
            - $docDouanier->getTotalValeur(DRCsvFile::CSV_LIGNE_CODE_AUTRES_VOLUMES_NON_VINIFIES_L12, null, $produitFilter)
            - $docDouanier->getTotalValeur(DRCsvFile::CSV_LIGNE_CODE_VOLUME_SANS_IG_L14, null, $produitFilter)
            - $docDouanier->getTotalValeur(DRCsvFile::CSV_LIGNE_CODE_USAGESIND_L16, null, $produitFilter) ;
    }

    public function getSuperficieHorsApportCoopFromDocumentProduction(TemplateFactureCotisationCallbackParameters $produitFilter) {
        $docDouanier = $this->getDocumentDouanierOlderThanMe();
        if (!$docDouanier) {
            return ;
        }
        $type = $docDouanier->type;
        if (!$type) {
            return ;
        }
        if ($type == DRCsvFile::CSV_TYPE_DR) {
            return $docDouanier->getTotalValeur(DRCsvFile::CSV_LIGNE_CODE_SUPERFICIE_L4, null, $produitFilter, DouaneProduction::FAMILLE_APPORTEUR_COOP_TOTAL);
        }
        if ($type == SV11CsvFile::CSV_TYPE_SV11) {
            return $docDouanier->getTotalValeur(SV11CsvFile::CSV_LIGNE_CODE_SUPERFICIE, null, $produitFilter);
        }
        if ($type == SV12CsvFile::CSV_TYPE_SV12) {
            return $docDouanier->getTotalValeur(SV12CsvFile::CSV_LIGNE_CODE_SUPERFICIE, null, $produitFilter);
        }
        throw new sfException("type de document douanier $type n'est pas supporté");
    }

    public function getNbApporteursPlusOneFromDouane($produitFilter = null) {
        $douane = $this->getDocumentDouanierOlderThanMe();
        if (!$douane || $douane->type == DRClient::TYPE_COUCHDB ) {
            return 0;
        }
        $apporteurs = $douane->getNbApporteurs($produitFilter->getParameters('appellations'));
        if (!$apporteurs) {
            return 0;
        }
        return $apporteurs + 1;
    }

    public function getVolumeLotsFacturables(TemplateFactureCotisationCallbackParameters $produitFilter){

        return $this->getVolumeRevendiqueLots($produitFilter);
    }

    public function isVolumeLotsFacturablesInRange($min = null,$max = null){
      $total = $this->getMaster()->getVolumeLotsFacturables();
      if($total < 0){ return false; }
      if(!$max && $total > $min){ return true; }
      if($total > $min && $total <= $max){ return true; }
      return false;
    }

    public function getNbLieuxPrelevements(){
        return 1;
    }

    /* Volume : L5 - L16 + L19 */
    /* Une partie du VCI (L19) est dans la L16 */
    public function getQuantiteVolumeRecolteClair(TemplateFactureCotisationCallbackParameters $parameters)
    {
        $volume = 0;
        $docDouanier = $this->getDocumentDouanier();

        if (!$docDouanier || $docDouanier->type != DRCsvFile::CSV_TYPE_DR) {
            return;
        }

        $L5  = $docDouanier->getTotalValeur(DRCsvFile::CSV_LIGNE_CODE_RECOLTE_L5, null, $parameters);
        $L16 = $docDouanier->getTotalValeur(DRCsvFile::CSV_LIGNE_CODE_USAGESIND_L16, null, $parameters);
        $L19 = $docDouanier->getTotalValeur(DRCsvFile::CSV_LIGNE_CODE_VCI_L19, null, $parameters);

        return $L5 - $L16 + $L19;
    }

    public function getQuantiteVolumeRevendique(TemplateFactureCotisationCallbackParameters $parameters)
    {
        $volume = 0;
        foreach ($this->declaration->getProduitsFilteredBy($parameters) as $produit) {
            $volume += $produit->getTotalVolumeRevendique();
        }

        foreach($this->getDeletedLots() as $lot) {
            $volume -= $lot->volume;
        }

        return $volume;
    }

    public function getQuantiteVolumeRevendiqueRecolte(TemplateFactureCotisationCallbackParameters $parameters)
    {
        if (DRevClient::getInstance()->matchFilterDrev($this, $parameters) === false) {
            return null;
        }
        $volume = 0;
        foreach ($this->declaration->getProduitsFilteredBy($parameters) as $produit) {
            $volume += $produit->getVolumeRevendiqueIssuRecolte();
        }

        return $volume;
    }

    public function getQuantiteVolumeRevendiqueVci(TemplateFactureCotisationCallbackParameters $parameters)
    {
        $volume = 0;
        foreach ($this->declaration->getProduitsFilteredBy($parameters) as $produit) {
            $volume += $produit->getVolumeRevendiqueIssuVci();
        }

        return $volume;
    }

    public function getQuantiteSuperficieRevendique()
	{
		return $this->declaration->getTotalTotalSuperficie();
	}

    public function getQuantiteVolumeRecolte(TemplateFactureCotisationCallbackParameters $parameters) {
        $docDouanier = $this->getDocumentDouanier();

        if (!$docDouanier || $docDouanier->type != DRCsvFile::CSV_TYPE_DR) {
            return;
        }

        return $docDouanier->getTotalValeur(DRCsvFile::CSV_LIGNE_CODE_RECOLTE_L5, null, $parameters);
    }

    /** @see getQuantiteSuperficeRecolte Typo dans la fonction originelle */
    public function getQuantiteSuperficieRecolte(TemplateFactureCotisationCallbackParameters $parameters) {
        return $this->getQuantiteSuperficeRecolte($parameters);
    }

    public function getQuantiteSuperficeRecolte(TemplateFactureCotisationCallbackParameters $parameters) {

        if (DRevClient::getInstance()->matchFilterDrev($this, $parameters) === false) {
            return null;
        }
        $docDouanier = $this->getDocumentDouanier();

        if (!$docDouanier || $docDouanier->type != DRCsvFile::CSV_TYPE_DR) {
            return;
        }

        return $docDouanier->getTotalValeur(DRCsvFile::CSV_LIGNE_CODE_RECOLTE_L4, null, $parameters);
    }

    public function getQuantiteSuperficieRecolteWithBailleur(TemplateFactureCotisationCallbackParameters $parameters) {

        if (DRevClient::getInstance()->matchFilterDrev($this, $parameters) === false) {
            return null;
        }
        $docDouanier = $this->getDocumentDouanier();

        if (!$docDouanier || $docDouanier->type != DRCsvFile::CSV_TYPE_DR) {
            return;
        }

        return $docDouanier->getTotalValeur(DRCsvFile::CSV_LIGNE_CODE_RECOLTE_L4_ORIGINALE, null, $parameters);
    }

    public function getQuantiteVolumeVendue(TemplateFactureCotisationCallbackParameters $parameters) {
        $docDouanier = $this->getDocumentDouanier();
        if (!$docDouanier || $docDouanier->type != DRCsvFile::CSV_TYPE_DR) {
            return;

        }
        return $docDouanier->getTotalValeur(DRCsvFile::CSV_LIGNE_CODE_ACHETEUR_RAISINS_L6, null, $parameters) +
                $docDouanier->getTotalValeur(DRCsvFile::CSV_LIGNE_CODE_ACHETEUR_MOUTS_L7, null, $parameters);
    }

    /**** MOUVEMENTS ****/

    public function getTemplateFacture($region = null) {
        return TemplateFactureClient::getInstance()->findByCampagne($this->getPeriode(), $region);
    }

    public function getMouvementsFactures() {

        return $this->_get('mouvements');
    }

    public function getMouvementsFacturesCalcule($region = null) {
      $templateFacture = $this->getTemplateFacture($region);
      if(!$templateFacture) {
          return array();
      }

      $cotisations = $templateFacture->generateCotisations($this);

      if($this->hasVersion()) {
          $cotisationsPrec = $templateFacture->generateCotisations($this->getMother());
      }

      $identifiantCompte = $this->getIdentifiant();

      $mouvements = array();

      $rienAFacturer = true;

      foreach($cotisations as $cotisation) {
          if (
              (strpos($cotisation->getHash(), '%detail_identifiant%') !== false) &&
              ($cotisation->getConfigCallback() == 'getVolumeLotsFacturables' || $cotisation->getConfigCallback() == 'getVolumeRevendiqueLots')
             ) {
              throw new sfException("getVolumeLotsFacturables/getVolumeRevendiqueLots incompatibles avec %detail_identifiant%");
          }

          $mouvement = DRevMouvementFactures::freeInstance($this);
          $mouvement->detail_identifiant = $this->numero_archive;
          $mouvement->createFromCotisationAndDoc($cotisation, $this);
          $mouvement->detail_libelle = str_replace(array('%millesime_precedent%', '%millesime_courant%'), array($this->getPeriode() - 1, $this->getPeriode()), $mouvement->detail_libelle);

          $cle = str_replace(['%detail_identifiant%', '%millesime%'], [$mouvement->detail_identifiant, $this->getPeriode()], $cotisation->getHash());
          if(isset($cotisationsPrec[$cle]) && $cotisation->getConfigCallback() != 'getVolumeRevendiqueNumeroDossier') {
              $mouvement->quantite = $mouvement->quantite - $cotisationsPrec[$cle]->getQuantite();
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
          return array();

      }

      return array($identifiantCompte => $mouvements);
    }

    public function getDeletedLots() {
        $deleted = array();
        foreach($this->getDiffLotVolume() as $k => $v) {
            if (strpos($k, '/unique_id') === false) {
                continue;
            }
            if (!$this->getLot($v)) {
                $deleted[] = $v;
            }
        }
        $lots = array();
        foreach($deleted as $unique_id) {
            if(!$this->getMother()->getLot($unique_id)) {
                continue;
            }
            $lots[] = $this->getMother()->getLot($unique_id);
        }
        return $lots;
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

    /**** FCT de FACTURATION ****/

    public function isDeclarantFamille(TemplateFactureCotisationCallbackParameters $familleFilter){
        if(!$familleFilter){

            return false;
        }
        if(!$this->declarant->famille){

            return false;
        }

        $familleFilter = $familleFilter->getParameters('famille');

        $familleFilterMatch = preg_replace("/^NOT /", "", $familleFilter, -1, $exclude);
		$exclude = (bool) $exclude;
        $regexpFilter = "#^(".implode("|", explode(",", $familleFilterMatch)).")$#";

        if(!$exclude && preg_match($regexpFilter, $this->declarant->famille)) {

			return true;
		}

        if($exclude && !preg_match($regexpFilter, $this->declarant->famille)) {

			return true;
		}

        return false;
    }

    /**** MOUVEMENTS LOTS ****/

    public function getLot($uniqueId) {

        foreach($this->lots as $lot) {
            if($lot->getUniqueId() != $uniqueId) {

                continue;
            }

            return $lot;
        }

        return null;
    }

    public function clearMouvementsLots(){
        $this->remove('mouvements_lots');
        $this->add('mouvements_lots');
    }

    public function addMouvementLot($mouvement) {

        return $this->mouvements_lots->add($mouvement->declarant_identifiant)->add($mouvement->getUnicityKey(), $mouvement);
    }

    public function hasLotsUtilises() {
        foreach($this->lots as $lot) {
            if($lot->hasBeenEdited()) {
                continue;
            }

            if($lot->isAffecte()) {
                return true;
            }

            if($lot->isChange()) {
                return true;
            }
        }

        return false;
    }

    public function generateMouvementsLots()
    {
        $this->clearMouvementsLots();

        if (!$this->isValideeOdg()) {
          return;
        }
        $commission_date = [];
        foreach ($this->lots as $lot) {
            if($lot->hasBeenEdited()) {
                continue;
            }

            if(!$this->isMaster() && $this->getMaster()->isValideeOdg() && (!$this->getMaster()->getLot($lot->unique_id) || $this->getMaster()->getLot($lot->unique_id)->id_document != $lot->id_document)) {
                continue;
            }

            $lot->updateDocumentDependances();
            $lot->updateStatut();

            $this->addMouvementLot($lot->buildMouvement(Lot::STATUT_REVENDIQUE));

            if ($lot->elevage === true) {
                $this->addMouvementLot($lot->buildMouvement(Lot::STATUT_ELEVAGE_EN_ATTENTE));
                continue;
            }
            if ($lot->eleve) {
                $this->addMouvementLot($lot->buildMouvement(Lot::STATUT_ELEVE, '', $lot->eleve));
            }

            if($lot->isChange()) {
                $this->addMouvementLot($lot->buildMouvement(Lot::STATUT_CHANGE_SRC, $lot->getLibelle()));
            } elseif(!$lot->isAffecte()) {
                $this->addMouvementLot($lot->buildMouvement(Lot::STATUT_CHANGEABLE));
            }

            if($lot->isAffecte()) {
                $commission_date[$lot->date_commission] = $lot->date_commission;
                $this->addMouvementLot($lot->buildMouvement(Lot::STATUT_AFFECTE_SRC, '1er passage'));
                continue;
            }
            if ($lot->isAffectable()) {
                if (!$lot->isChange()) {
                    $this->addMouvementLot($lot->buildMouvement(Lot::STATUT_AFFECTABLE));
                }
            }else{
                $this->addMouvementLot($lot->buildMouvement(Lot::STATUT_NONAFFECTABLE));
            }
        }
        if (count(array_keys($commission_date)) == 1) {
            if (!$this->exist('date_commission')) {
                $this->add('date_commission');
            }
            $this->date_commission = array_key_first($commission_date);
        }
    }

    /**** FIN DES MOUVEMENTS LOTS ****/

    /**** PIECES ****/

    public function getAllPieces() {
    	$complement = ($this->isPapier())? '(Papier)' : '(Télédéclaration)';
      $date = null;
      if ($this->getValidation()) {
        $dt = new DateTime($this->getValidation());
        $date = $dt->format('Y-m-d');
      }
    	return (!$this->getValidation())? array() : array(array(
    		'identifiant' => $this->getIdentifiant(),
    		'date_depot' => $date,
    		'libelle' => 'Revendication des produits '.$this->periode.' '.$complement,
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

    public function getCategorie(){
      return strtolower($this->type);
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

    public function isTeledeclare() {
        return !$this->isPapier();
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
        $id = 'DREV-' . $this->identifiant . '-' . $this->periode;
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

    public function getDiffLotVolume() {
        $diff = $this->getDiffWithMother(true);
        $diffLot = array();
        foreach($diff as $k => $v) {
            if (strpos($k, '/lots/') !== false && (strpos($k, '/volume') !== false || strpos($k, '/unique_id') !== false)) {
                $diffLot[$k] = $v;
            }
        }
        return $diffLot;
    }

    public function getDiffWithMother($both_directions = false) {

        return $this->version_document->getDiffWithMother($both_directions);
    }

    public function isModifiedMother($hash_or_object, $key = null, $both_directions = false) {

        return $this->version_document->isModifiedMother($hash_or_object, $key);
    }

    public function generateRectificative() {

        return $this->version_document->generateRectificative();
    }

    public function generateModificative() {

        if (!DRevConfiguration::getInstance()->isModificativeEnabled()) {
            throw new sfException('Drev Modificative non permise');
        }

        $drev = $this->version_document->generateModificative();
        try {
            $drev->resetAndImportFromDocumentDouanier();
        } catch(Exception $e) {

        }
        return $drev;
    }

    public function verifyGenerateModificative() {

        return $this->version_document->verifyGenerateModificative();
    }


    public function listenerGenerateVersion($document) {
        $document->constructId();
        $document->clearMouvementsLots();
        $document->clearMouvementsFactures();
        $document->devalidate();
        $document->remove('date_depot');
        foreach ($document->getProduitsLots() as $produit) {
          if($produit->exist("validation_odg") && $produit->validation_odg){
            $produit->validation_odg = null;
          }
        }
    }

    public function generateNextVersion() {

        throw new sfException("Not use");
    }

    public function listenerGenerateNextVersion($document) {
        throw new sfException("Not use");
    }

    public function getSuivante() {

        throw new sfException("Not use");
    }

    public function isValidee() {

        return boolval($this->validation);
    }

    public function isValideeOdg() {

        return boolval($this->getValidationOdg());
    }

    public function isLotsEditable(){
      return $this->isValideeOdg() && $this->isValidee();
    }

    /**** FIN DE VERSION ****/

    public function getDate() {
      return $this->periode.'-12-10';
    }

    public function hasDenominationAuto($const, $total = false) {
        if(!$this->exist("denomination_auto")) {
            return false;
        }
        if (!$total) {
            return strpos($this->getDenominationAuto(false), $const) !== false;
        }
        return $this->getDenominationAuto(false) == $const;
    }

    public function getDenominationAuto($to_array = true ){
        if(!$this->exist('denomination_auto')) {

            return ($to_array) ? array() : null;
        }
        $d = $this->_get('denomination_auto');
        if (strpos($d, DRevClient::DENOMINATION_BIO_PARTIEL_DEPRECATED) !== false) {
            $d = DRevClient::DENOMINATION_BIO.'|'.DRevClient::DENOMINATION_CONVENTIONNEL;
            $this->_set('denomination_auto', $d);
        }
        if (strpos($d, DRevClient::DENOMINATION_BIO_TOTAL_DEPRECATED) !== false) {
            $d = DRevClient::DENOMINATION_BIO;
            $this->_set('denomination_auto', $d);
        }
        if ($to_array){
            return explode('|', $d);
        }
        return $d;
    }

    public function setDenominationAuto($d) {
        if (is_array($d)) {
            $d = implode('|', $d);
        }
        return $this->_set('denomination_auto', $d);
    }

    public function getDocumentsAEnvoyer() {
        $documents = array();

        foreach($this->getOrAdd('documents') as $document) {
            if($document->statut != DRevDocuments::STATUT_EN_ATTENTE) {
                continue;
            }

            $documents[$document->getKey()] = $document;
        }

        return $documents;
    }

    public function getNonHabilitationINAO() {
        try {
            return DRevClient::getInstance()->getNonHabilitationINAO($this);
        }catch(Exception $e) {
            return array();
        }
    }

    public function getNonHabilitationODG() {
        $date = $this->date_depot;
        if (!$date) {
            $date = date('Y-m-d');
        }
        $habilitation = HabilitationClient::getInstance()->findPreviousByIdentifiantAndDate($this->identifiant, $date);
        $nonHabilitationODG = array();
        foreach($this->getProduits() as $hash_c => $produit_c) {
            $produit = $produit_c->getCepage();
            $hash = $produit->getHash();
            if (!$habilitation || !$habilitation->isHabiliteFor(str_replace('/VDN/appellations/VDR', '/TRANQ/appellations/RTA', $produit->getConfig()->getAppellation()->getHash()), HabilitationClient::ACTIVITE_VINIFICATEUR)) {
                $nonHabilitationODG[$hash] = $produit;
            }
        }
        return $nonHabilitationODG;
    }

    public function hasProduitWithMutageAlcoolique() {
        foreach($this->getProduits() as $produit) {

            if($produit->getConfig()->hasMutageAlcoolique()) {
                return true;
            }
        }

        return false;
    }

    public function setDateDegustationSouhaitee($date) {
        $this->_add('date_degustation_voulue', $date);
    }

    public function getProduitsWithReserveInterpro($region = null) {
        $produits = array();
        foreach($this->getProduits($region) as $p) {
            if (!$p->getConfig()->getRendementReserveInterpro()) {
                continue;
            }
            $produit_libelle = $p->getCepage()->getLibelleComplet();
            if (!isset($produits[$produit_libelle])) {
                $produits[$produit_libelle] = [];
            }
            $produits[$produit_libelle][] = $p;
        }
        $produit_ret = array();
        foreach($produits as $l => $prods) {
            if (count($prods) < 1) {
                continue;
            }
            $meta_prod = clone $prods[0];
            $meta_prod->volume_revendique_total = 0;
            $meta_prod->superficie_revendique = 0;
            $meta_prod->libelle = $l;
            foreach($prods as $p) {
                $meta_prod->volume_revendique_total += $p->volume_revendique_total;
                $meta_prod->superficie_revendique += $p->superficie_revendique;
            }
            $produit_ret[] = $meta_prod;
        }
        return $produit_ret;
    }

    public function hasProduitsReserveInterpro($region = null) {
        return (count($this->getProduitsWithReserveInterpro($region)));
    }

    public function getBigDocumentSize() {

        return -1;
    }

    public function getHashRegexp($hash_produit_regexp) {
        $hashes = [];
        foreach($this->getProduits() as $hash => $produit) {
            if (VIP2C::isHashMatch($hash_produit_regexp, $hash)) {
                $hashes[] = VIP2C::cleanHash($produit->getCepage()->getHash());
            }
        }
        return $hashes;
    }

    public function getProduitsHashWithVolumeSeuil() {
        $p = array();
        $parLot = $this->declaration->getConfig()->isRevendicationParLots();
        foreach(VIP2C::getProduitsHashWithVolumeSeuil($this->declarant->cvi, $this->getDefaultMillesime()) as $hash_produit) {
            $hashes = $this->getHashRegexp($hash_produit);
            if (!$hashes) {
                continue;
            }

            if (!$parLot) {
                $p += $hashes;
                continue;
            }

            $toadd = false;
            foreach ($this->getLots() as $l) {
                if (!VIP2C::isHashMatch($hash_produit, $l->produit_hash)) {
                    continue;
                }

                if ($l->millesime != $this->getDefaultMillesime()) {
                    continue;
                }

                $p[] = VIP2C::cleanHash($l->produit_hash);
            }
        }
        return array_unique($p);
    }

    public function hasVolumeSeuilAndSetIfNecessary(){

        if(!VIP2C::hasVolumeSeuil()) {
            return false;
        }

        if(!($this->getCampagne() >= VIP2C::getConfigCampagneVolumeSeuil())){
            return false;
        }

        $ret = false;
        foreach($this->getProduitsHashWithVolumeSeuil() as $hash_produit) {

            if(!isset($this->document->declaration[$hash_produit])){
                continue;
            }

            if(!$this->document->declaration->get($hash_produit)->exist('DEFAUT')) {
                continue;
            }

            $produit = $this->document->declaration->get($hash_produit)->DEFAUT;

            if(!$produit->exist('volume_revendique_seuil') && !(VIP2C::getVolumeSeuilProduitFromCSV($this->declarant->cvi, $this->getDefaultMillesime(), $hash_produit))) {
                continue;
            }
            if($produit->exist('volume_revendique_seuil')){
                $ret = true;
                continue;
            }

            $volumeSeuil = VIP2C::getVolumeSeuilProduitFromCSV($this->declarant->cvi, $this->getDefaultMillesime(), $hash_produit);
            if ($volumeSeuil) {
                $produit->add('volume_revendique_seuil',floatval($volumeSeuil));
                $this->save();
            }
        }
        return $ret;

    }

    public function getVolumeRevendiqueSeuil($hash){
        if(!VIP2C::hasVolumeSeuil()) {
            return null;
        }

        if(!isset($this->document->declaration[$hash])){
            return null;
        }

        if (! $this->document->declaration->get($hash)->exist('DEFAUT')) {
            return null;
        }

        $produit = $this->document->declaration->get($hash)->DEFAUT;

        if(! $produit->exist('volume_revendique_seuil')){
            return null;
        }
        return $produit->volume_revendique_seuil;

    }

    public function getVolumeCommercialisableLibre($hash){
        $volumeSeuil = $this->getVolumeRevendiqueSeuil($hash);
        return($volumeSeuil-($volumeSeuil*0.1)); #les prévenir à 10%
    }

    public function hasLotsProduitFilter($hash_or_filter) {
        foreach ($this->lots as $lot) {
            if(strpos($lot->produit_hash, $hash_or_filter) !== false) {
                return true;
            }
        }
        return false;
    }

    public function cloneDRevForOneDossier($numero_dossier)
    {
        $drev = clone $this;
        $drev->_rev = null;

        $lots = array_filter($drev->lots->toArray(), function ($lot) use ($numero_dossier) {
            return $numero_dossier === $lot->numero_dossier;
        });

        $drev->remove('lots');
        $drev->add('lots', array_values($lots));

        return $drev;
    }

    public function getDefaultMillesime() {
        return substr($this->campagne, 0, 4);
    }
}
