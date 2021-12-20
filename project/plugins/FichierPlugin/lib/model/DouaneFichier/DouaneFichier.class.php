<?php

class DouaneFichier extends Fichier implements InterfaceMouvementFacturesDocument, InterfaceDeclarantDocument {

    protected $mouvement_document = null;
    protected $declarant_document = null;
    protected $enhanced_donnees = null;

    public function getPeriode() {

        return $this->campagne;
    }

    public function __clone() {
		parent::__clone();
	}

    protected function initDocuments() {
        parent::initDocuments();
        $this->mouvement_document = new MouvementFacturesDocument($this);
        $this->declarant_document = new DeclarantDocument($this);
    }


    public function save() {
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

      $drev = DRevClient::getInstance()->findMasterByIdentifiantAndCampagne($this->identifiant, $this->getCampagne());

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

    public function getEnhancedDonnees() {
        if (isset($this->enhanced_donnees)) {
            return $this->enhanced_donnees;
        }
        $this->generateDonnees();
        $this->enhanced_donnees = array();
        $colonnesid = array();
        $colonneid = 0;
        foreach($this->donnees as $i => $donnee) {
            $d = (object) $donnee->toArray();
            $d->produit_conf = $this->configuration->declaration->get($donnee->produit);
            $p = array();
            if ($donnee->bailleur && $d->bailleur_etablissement = EtablissementClient::getInstance()->find($donnee->bailleur)) {
                $p[] = $d->bailleur_etablissement->raison_sociale;
                $p[] = $d->bailleur_etablissement->ppm;
            } else {
                $p[] = null;
                $p[] = null;
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
            $this->enhanced_donnees[] = $d;
        }
        return $this->enhanced_donnees;
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
        $item = $this->donnees->add();
        $item->produit = $hash;
        $item->produit_libelle = $this->getConfiguration()->declaration->get($hash)->getLibelleComplet();
        $item->complement = $data[DouaneCsvFile::CSV_PRODUIT_COMPLEMENT];
        $item->categorie = $data[DouaneCsvFile::CSV_LIGNE_CODE];
        $item->valeur = VarManipulator::floatize($data[DouaneCsvFile::CSV_VALEUR]);
        $item->colonneid = $data[DouaneCsvFile::CSV_COLONNE_ID];
        if ($data[DouaneCsvFile::CSV_TIERS_CVI]) {
            if ($tiers = EtablissementClient::getInstance()->findByCvi($data[DouaneCsvFile::CSV_TIERS_CVI])) {
                $item->tiers = $tiers->_id;
            }
        }
        if ($data[DouaneCsvFile::CSV_BAILLEUR_PPM]) {
            if ($tiers = EtablissementClient::getInstance()->findByPPM($data[DouaneCsvFile::CSV_BAILLEUR_PPM])) {
                $item->bailleur = $tiers->_id;
            }
        }

        return $item;
    }

    public function getCategorie(){
        return strtolower($this->type);
    }

    public function calcul($formule, $produitFilter = null) {
        $calcul = $formule;
        $numLignes = preg_split('|[\-+*\/() ]+|', $formule, -1, PREG_SPLIT_NO_EMPTY);
        foreach($numLignes as $numLigne) {
            $datas[$numLigne] = $this->getTotalValeur($numLigne, $produitFilter);
        }

        foreach($datas as $numLigne => $value) {
            $calcul = str_replace($numLigne, $value, $calcul);
        }

        return eval("return $calcul;");
    }

    public function getTotalValeur($numLigne, $produitFilter = null) {
        $value = 0;

        $produitFilter = preg_replace("/^NOT /", "", $produitFilter, -1, $produitExclude);
        $produitExclude = (bool) $produitExclude;
        $regexpFilter = "#(".implode("|", explode(",", $produitFilter)).")#";

        foreach($this->donnees as $donnee) {
            if($produitFilter && !$produitExclude && !preg_match($regexpFilter, $donnee->produit)) {
                continue;
            }
            if($produitFilter && $produitExclude && preg_match($regexpFilter, $donnee->produit)) {
                continue;
            }
            if($donnee->categorie != str_replace("L", "", $numLigne)) {
                continue;
            }

            $value += VarManipulator::floatize($donnee->valeur);
        }

        return $value;
    }
    public function getNbApporteurs() {
        $apporteurs = array();
        foreach($this->donnees as $donnee) {
            if ($donnee->tiers) {
                $apporteurs[$donnee->tiers] = 1;
            }
        }
        return count($apporteurs);
    }

    public function getProduits()
    {
        $donnees = [];

        foreach ($this->donnees as $ligne) {
            $produit_key = $ligne['produit'];

            if (array_key_exists($produit_key, $donnees) === false) {
                $donnees[$produit_key] = [];
                $donnees[$produit_key]['libelle'] = $ligne['produit_libelle'];
                $donnees[$produit_key]['lignes'] = [];
            }

            if (array_key_exists($ligne['categorie'], $donnees[$produit_key]['lignes']) === false) {
                $donnees[$produit_key]['lignes'][$ligne['categorie']]['val'] = 0;
                if (in_array($ligne['categorie'], ['04', '04b'])) {
                    $unit = 'ha';
                    $decimals = 4;
                } else {
                    $unit = 'hl';
                    $decimals = 2;
                }
                $donnees[$produit_key]['lignes'][$ligne['categorie']]['unit'] = $unit;
                $donnees[$produit_key]['lignes'][$ligne['categorie']]['decimals'] = $decimals;
            }

            $donnees[$produit_key]['lignes'][$ligne['categorie']]['val'] += $ligne['valeur'];
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
                $synthese[$hash_produit]['libelle'] = ConfigurationClient::getCurrent()->declaration->get($hash)->getCouleur()->getLibelleComplet();
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

    public function getProduitsDetail()
    {
        $donnees = [];

        // Produits :
        $donnees['lignes'] = array_column($this->donnees->toArray(1,0), 'categorie');
        sort($donnees['lignes']);
        $donnees['lignes'] = array_unique($donnees['lignes']);
        $donnees['produits'] = [];

        foreach ($this->donnees as $entry) {
            $produit = $entry->produit;
            $categorie = $entry->categorie;

            if (array_key_exists($produit, $donnees['produits']) === false) {
                $donnees['produits'][$produit]['lignes'] = [];
                $donnees['produits'][$produit]['libelle'] = $entry->produit_libelle;
            }

            if (array_key_exists($categorie, $donnees['produits'][$produit]['lignes']) === false) {
                $donnees['produits'][$produit]['lignes'][$categorie] = [];
                $donnees['produits'][$produit]['lignes'][$categorie]['val'] = 0;
            }

            $donnees['produits'][$produit]['lignes'][$categorie]['val'] += $entry->valeur;
            $donnees['produits'][$produit]['lignes'][$categorie]['unit'] = (in_array($entry->categorie, ['04', '04b'])) ? 'ha' : 'hl';
            $donnees['produits'][$produit]['lignes'][$categorie]['decimals'] = (in_array($entry->categorie, ['04', '04b'])) ? 4 : 2;
        }

        // potentiellement, des lignes n'existent pas pour certains produits
        foreach ($donnees['produits'] as $key => &$value) {
            $missing = array_diff($donnees['lignes'], array_keys($value['lignes']));
            if (count($missing)) {
                foreach ($missing as $k => $m) {
                    $value['lignes'][$m] = ['val' => '—'];
                }
            }
        }

        ksort($donnees['produits'], SORT_NUMERIC);
        foreach ($donnees['produits'] as &$array) {
            ksort($array['lignes'], SORT_STRING);
        }

        return $donnees;
    }

    public function validateOdg($date = null)
    {
        $this->add('validation_odg');
        $this->validation_odg = ($date) ?: date('Y-m-d');
    }
}
