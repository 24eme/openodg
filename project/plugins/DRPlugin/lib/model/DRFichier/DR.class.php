<?php
/**
 * Model for DR
 *
 */

class DR extends BaseDR implements InterfaceMouvementDocument {

	protected $mouvement_document = null;

	public function __construct() {
		parent::__construct();
	}

	public function __clone() {
		parent::__clone();
	}

	protected function initDocuments() {
		parent::initDocuments();
        $this->mouvement_document = new MouvementDocument($this);
    }

	public function constructId() {
		$this->set('_id', 'DR-' . $this->identifiant . '-' . $this->campagne);
	}

    public static function isPieceEditable($admin = false) {
    	return ($admin)? true : false;
    }

    public function generateDonnees() {
    	$export = new ExportDRCSV($this, false);
    	$csv = explode(PHP_EOL, $export->export());
    	if (!$this->exist('donnees') || count($this->donnees) < 1) {
    		$donnees = $this->add('donnees');
    		$generate = false;
	    	foreach ($csv as $datas) {
	    		$data = str_getcsv($datas, ";");
	    		if ($data && isset($data[DouaneCsvFile::CSV_PRODUIT_CERTIFICATION]) && !empty($data[DouaneCsvFile::CSV_PRODUIT_CERTIFICATION])) {
	    			$generate = true;
	    			$item = $donnees->add();
	    			$item->produit = "certifications/".$data[DouaneCsvFile::CSV_PRODUIT_CERTIFICATION]."/genres/".$data[DouaneCsvFile::CSV_PRODUIT_GENRE]."/appellations/".$data[DouaneCsvFile::CSV_PRODUIT_APPELLATION]."/mentions/".$data[DouaneCsvFile::CSV_PRODUIT_MENTION]."/lieux/".$data[DouaneCsvFile::CSV_PRODUIT_LIEU]."/couleurs/".$data[DouaneCsvFile::CSV_PRODUIT_COULEUR]."/cepages/".$data[DouaneCsvFile::CSV_PRODUIT_CEPAGE];
	    			$item->complement = $data[DouaneCsvFile::CSV_PRODUIT_COMPLEMENT];
	    			$item->categorie = $data[DouaneCsvFile::CSV_LIGNE_CODE];
	    			$item->valeur = $data[DouaneCsvFile::CSV_VALEUR];
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
	    		}
	    	}
	    	return $generate;
    	}
    	return false;
    }

	public function getCategorie(){
		return strtolower($this->type);
	}

	/**** MOUVEMENTS ****/

    public function getTemplateFacture() {
        return TemplateFactureClient::getInstance()->find("TEMPLATE-FACTURE-AOC-".$this->getCampagne());
    }

    public function getMouvements() {

        return $this->_get('mouvements');
    }

    public function getMouvementsCalcule() {
      $templateFacture = $this->getTemplateFacture();

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
          $mouvement = DRMouvement::freeInstance($this);
          $mouvement->fillFromCotisation($cotisation);
          $mouvement->facture = 0;
          $mouvement->facturable = 1;
          $mouvement->date = $this->getCampagne().'-12-10';
          $mouvement->date_version = $mouvement->date;
          $mouvement->version = $this->version;

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

    public function getMouvementsCalculeByIdentifiant($identifiant) {

        return $this->mouvement_document->getMouvementsCalculeByIdentifiant($identifiant);
    }

    public function generateMouvements() {

        return $this->mouvement_document->generateMouvements();
    }

    public function findMouvement($cle, $id = null){
      return $this->mouvement_document->findMouvement($cle, $id);
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

    public function clearMouvements(){
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

}
