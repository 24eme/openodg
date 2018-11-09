<?php
/**
 * Model for RegistreVCI
 *
 */

class RegistreVCI extends BaseRegistreVCI implements InterfaceProduitsDocument, InterfaceMouvementDocument, InterfacePieceDocument {

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
          $this->mouvement_document = new MouvementDocument($this);
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
      }

      public function clear() {
        $this->remove('declaration');
        $this->remove('lignes');
        $this->remove('mouvements');
        $this->superficies_facturables = 0;
      }

      protected function doSave() {
        $this->piece_document->generatePieces();
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

      public function getSurfaceFacturable() {

          return ($this->superficies_facturables > 0)? round($this->superficies_facturables / 100, 4) : 0;
      }

      public function getDRev() {
        $drev = DRevClient::getInstance()->find('DREV-'.$this->identifiant.'-'.$this->campagne);
        return $drev;
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

          $identifiantCompte = "E".$this->getIdentifiant();

          $mouvements = array();

          $rienAFacturer = true;

          foreach($cotisations as $cotisation) {
              $mouvement = RegistreVCIMouvement::freeInstance($this);
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

      public function getMouvementsCalculeByIdentifiant($identifiant) {

          return $this->mouvement_document->getMouvementsCalculeByIdentifiant($identifiant);
      }

      public function generateMouvements() {
          if(!$this->getTemplateFacture()) {

              return false;
          }

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

      public function getValidation() {

          return true;
      }

}
