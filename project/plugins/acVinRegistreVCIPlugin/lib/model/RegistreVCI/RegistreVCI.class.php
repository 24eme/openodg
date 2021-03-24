<?php
/**
 * Model for RegistreVCI
 *
 */

class RegistreVCI extends BaseRegistreVCI implements InterfaceProduitsDocument, InterfaceMouvementFacturesDocument, InterfacePieceDocument {

      protected $piece_document = null;
      protected $mouvement_document = null;

      public function __construct() {
          parent::__construct();
          $this->initDocuments();
      }

      public function __clone() {
          parent::__clone();
          $this->initDocuments();
      }

      public function constructId() {
          $id = RegistreVCIClient::TYPE_COUCHDB.'-' . $this->identifiant . '-' . $this->campagne;
          if($this->version) {
              $id .= "-".$this->version;
          }
          $this->set('_id', $id);
      }

      public function getConfiguration() {

          return ConfigurationClient::getInstance()->getConfiguration($this->campagne);
      }

      protected function initDocuments() {
          $this->piece_document = new PieceDocument($this);
          $this->mouvement_document = new MouvementFacturesDocument($this);
      }

      public function initDoc($identifiant, $campagne) {
          $this->identifiant = $identifiant;
          $this->campagne = $campagne;
          $etablissement = $this->getEtablissementObject();
          $this->initDocuments();
      }

      public function getProduits() {
        $produits = array();
        foreach ($this->declaration as $k => $p) {
          $produits['declaration/'.$k] = $p;
        }
        return $produits;
      }

      public function getProduitDetails() {
        $produits = array();
        foreach ($this->declaration as $k => $p) {
          foreach ($p->details as $key => $d) {
            $produits[$d->getHash()] = $d;
          }
        }
        return $produits;
      }

      public function getConfigProduits() {

          return $this->getConfiguration()->declaration->getProduits();
      }

      public function getEtablissementObject() {
        return EtablissementClient::getInstance()->findByIdentifiant($this->identifiant);
      }

      public function clotureStock() {
          if(!$this->isStockUtiliseEntierement()) {
              return;
          }
          foreach($this->getProduits() as $produit) {
                $produit->_set('stock_final', 0);
                foreach($produit->details as $detail) {
                    $detail->_set('stock_final', 0);
                }
          }
      }

      public function addLigne($produit, $mouvement_type, $volume, $lieu) {
          if (is_string($produit)) {
            $hproduit = preg_replace('/\/*declaration\//', '', $produit);
            $produit = $this->getConfiguration()->declaration->get($hproduit);
          }else{
            $hproduit = preg_replace('/\/*declaration\//', '', $produit->getHash());
          }
          $hproduit = preg_replace('|appellation_CREMANT/mention/lieu/couleur/.*|', 'appellation_CREMANT', $hproduit);
          $nDetail = $this->add('declaration')->add($hproduit)->addLigne($mouvement_type, $volume, $lieu);
          $mvt = $this->add('lignes')->add();
          $mvt->produit_hash = $hproduit;
          $mvt->produit_libelle = $nDetail->getLibelleProduit();
          $mvt->detail_hash = $nDetail->stockage_identifiant;
          $mvt->detail_libelle = $nDetail->getLibelle();
          $mvt->volume = $volume;
          $mvt->mouvement_type = $mouvement_type;
          $mvt->stock_resultant = $nDetail->stock_final;
          if (RegistreVCIClient::MOUVEMENT_SENS($mouvement_type) > 0) {
            $mvt->date = $this->campagne.'-10-15';
          }else{
            $mvt->date = ($this->campagne + 1).'-12-31';
          }
      	  $this->reorderProduits();
      }

            protected function reorderProduits() {
		              $produits = $this->getProduits();

			                $this->remove('declaration');
			                $this->add('declaration');

					          foreach($this->getConfigProduits() as $config) {
							              foreach($produits as $hash => $produit) {
									                      if(!preg_match("|".$produit->getHash()."|", $config->getHash())) {
												                          continue;
															                  }
		$this->declaration->add(str_replace("/declaration/", "", $produit->getHash()), $produit);
											                      unset($produits[$hash]);
													                  }
								                }

					          foreach($produits as $produit) {
							                $this->declaration->add(str_replace("/declaration/", "", $produit->getHash()), $produit->getData());
									          }

					          return $this->declaration;
					      }

      public function clear() {
        $this->remove('declaration');
        $this->remove('lignes');
        $this->remove('mouvements');
        $this->superficies_facturables = null;
      }

      public function save() {
          $this->superficies_facturables = $this->calculSurfaceFacturable();
          return parent::save();
      }

      protected function doSave() {
        $this->piece_document->generatePieces();
      }

      public function isStockUtiliseEntierement() {
          foreach($this->getProduitsWithPseudoAppelations() as $produit) {
              if(!$produit->isPseudoAppellation()) {
                  continue;
              }
              if(round($produit->stock_final, 4) == 0) {
                  continue;
              }

              return false;
          }

          return true;
      }

      public function generateSuivante() {
          $registreSuivant = clone $this;

          $registreSuivant->campagne = ($this->campagne + 1)."";
          $registreSuivant->remove('lignes');
          $registreSuivant->add('lignes');
          $registreSuivant->remove('mouvements');
          $registreSuivant->add('mouvements');
          $registreSuivant->remove('pieces');
          $registreSuivant->add('pieces');
          $registreSuivant->superficies_facturables = null;

          foreach($registreSuivant->getProduits() as $produit) {
            $produit->clear();
          }

           if(!$this->isStockUtiliseEntierement()) {
               throw new Exception("Génération impossible, tout le stock de l'année précédente n'a pas été utilisé");
            }

          foreach($this->getProduitDetails() as $detail) {
            $detailSuivant = $registreSuivant->get($detail->getHash());
            $detailSuivant->stock_precedent = $detail->rafraichi;
          }

          return $registreSuivant;
      }

      public function getTotalMouvement($mouvement) {
          $total = 0;

          foreach($this->getProduits() as $produit) {
              $total = $total + $produit->get($mouvement);
          }

          return round($total, 2);
      }

      public function getAllPieces() {
      	$title = 'Registre VCI';
      	return array(array(
      		'identifiant' => $this->getIdentifiant(),
      		'date_depot' => $this->campagne.'-12-31',
      		'libelle' => $title.' '.$this->campagne,
      		'mime' => Piece::MIME_HTML,
      		'visibilite' => 1,
      		'source' => null
      	));
      }

      public function generatePieces() {
      	return $this->piece_document->generatePieces();
      }

      public function generateUrlPiece($source = null) {
      	return null;
      }

      public static function getUrlVisualisationPiece($id, $admin = false) {
      	return sfContext::getInstance()->getRouting()->generate('registrevci_visualisation', array('id' => $id));
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

      public function getProduitsWithPseudoAppelations() {
        $produits = array();
        foreach ($this->declaration as $i => $p) {
          if (!isset($oldappellation) || $oldappellation != $p->getAppellation()->getLibelle()) {
            if (isset($oldappellation) && $oldappellation != $p->getAppellation()->getLibelle()) {
                $produits[] = $appellationproduit;
            }
            $oldappellation = $p->getAppellation()->getLibelle();
            $appellationproduit = RegistreVCIProduit::freeInstance($this);
            $appellationproduit->setIsPseudoAppellation($this, $p->getAppellation());
          }
          $appellationproduit->freeIncr('stock_precedent', $p->stock_precedent);
          $appellationproduit->freeIncr('constitue', $p->constitue);
          $appellationproduit->freeIncr('rafraichi', $p->rafraichi);
          $appellationproduit->freeIncr('complement', $p->complement);
          $appellationproduit->freeIncr('substitution', $p->substitution);
          $appellationproduit->freeIncr('destruction', $p->destruction);
          $appellationproduit->freeIncr('stock_final', $p->stock_final);
          if ($p->isProduitCepage()) {
            $produits[] = $p;
          }
        }
        $produits[] = $appellationproduit;
        return $produits;
      }

      public function calculSurfaceFacturable() {
          $surfaceFacturable = 0;

          foreach($this->getProduitsWithPseudoAppelations() as $p) {
              if(!$p || !$p->isPseudoAppellation()) {
                  continue;
              }
              if(!$p->stock_precedent && !$p->constitue)  {
                  continue;
              }
              $surfaceFacturable += (float)$p->getSuperficieFromDrev();
          }

          return $surfaceFacturable;
      }

      public function getSurfaceFacturable() {
          if(is_null($this->superficies_facturables)) {
              $this->superficies_facturables = $this->calculSurfaceFacturable();
          }

          return ($this->superficies_facturables > 0)? round($this->superficies_facturables / 100, 4) : 0;
      }

      public function getDRev() {
        $drev = DRevClient::getInstance()->find('DREV-'.$this->identifiant.'-'.$this->campagne);
        return $drev;
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

          $cotisations = $templateFacture->generateCotisations($this);

          if($this->hasVersion()) {
              $cotisationsPrec = $templateFacture->generateCotisations($this->getMother());
          }

          $identifiantCompte = "E".$this->getIdentifiant();

          $mouvements = array();

          $rienAFacturer = true;

          foreach($cotisations as $cotisation) {
              $mouvement = RegistreVCIMouvementFactures::freeInstance($this);
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

      public function getStockFinalTotal() {
          $stockFinal = 0;
          foreach($this->getProduitDetails() as $detail) {
              $stockFinal += $detail->stock_final;
          }

          return $stockFinal;
      }

      public function getStockPrecedentTotal() {
          $stockPrecedent = 0;
          foreach($this->getProduitDetails() as $detail) {
              $stockPrecedent += $detail->stock_precedent;
          }

          return $stockPrecedent;
      }

      public function clearMouvementsFactures(){
          $this->remove('mouvements');
          $this->add('mouvements');
      }

      /**** FIN DES MOUVEMENTS ****/

      public function hasVersion() {

          return false;
      }

      public function getValidation() {

          return true;
      }

}
