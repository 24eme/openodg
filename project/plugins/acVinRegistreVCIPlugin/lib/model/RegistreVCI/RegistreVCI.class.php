<?php
/**
 * Model for RegistreVCI
 *
 */

class RegistreVCI extends BaseRegistreVCI implements InterfaceProduitsDocument, InterfacePieceDocument {

      protected $piece_document = null;

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
      }

      public function initDoc($identifiant, $campagne) {
          $this->identifiant = $identifiant;
          $this->campagne = $campagne;
          $etablissement = $this->getEtablissementObject();
          $this->initDocuments();
      }

      public function getProduits() {
        return array();
      }

      public function getConfigProduits() {

          return $this->getConfiguration()->declaration->getProduits();
      }

      public function getEtablissementObject() {
        return EtablissementClient::getInstance()->findByIdentifiant($this->identifiant);
      }

      public function addMouvement($produit, string $mouvement_type, float $volume, string $lieu) {
          if (is_string($produit)) {
            $hproduit = preg_replace('/\/*declaration\//', '', $produit);
            $produit = $this->getConfiguration()->declaration->get($hproduit);
          }else{
            $hproduit = preg_replace('/\/*declaration\//', '', $produit->getHash());
          }
          $nDetail = $this->add('declaration')->add($hproduit)->addMouvement($mouvement_type, $volume, $lieu);
          $mvt = $this->add('mouvements')->add();
          $mvt->produit_hash = $hproduit;
          $mvt->produit_libelle = $produit->getLibelleComplet();
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
        $this->remove('mouvements');
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
            $appellationproduit->setIsPseudoAppellation($p->getAppellation());
          }
          $appellationproduit->freeIncr('constitue', $p->constitue);
          $appellationproduit->freeIncr('rafraichi', $p->rafraichi);
          $appellationproduit->freeIncr('complement', $p->complement);
          $appellationproduit->freeIncr('substitution', $p->substitution);
          $appellationproduit->freeIncr('destruction', $p->destruction);
          $appellationproduit->freeIncr('stock_final', $p->stock_final);
          $produits[] = $p;
        }
        $produits[] = $appellationproduit;
        return $produits;
      }



}
