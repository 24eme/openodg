<?php

class ChgtDenom extends BaseChgtDenom implements InterfaceDeclarantDocument, InterfacePieceDocument, InterfaceMouvementLotsDocument {

    const DEFAULT_KEY = 'DEFAUT';

    protected $declarant_document = null;
    protected $piece_document = null;

    public function __construct() {
        parent::__construct();
		    //TODO : supprimer cette goretterie réalisée pour la démo
		    $this->campagne = '2019';
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

    public function constructId() {
        $id = 'CHGTDENOM-' . $this->identifiant . '-' . $this->date;
        $this->set('_id', $id);
    }

    public function getConfiguration() {
        return ConfigurationClient::getInstance()->getConfiguration();
    }

    public function getConfigProduits() {
        return $this->getConfiguration()->declaration->getProduits();
    }

    public function initDoc($identifiant, $date) {
        $this->identifiant = $identifiant;
        $this->date = $date;
        $etablissement = $this->getEtablissementObject();
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

    public function validate($date = null) {
        if(is_null($date)) {
            $date = date('Y-m-d');
        }
        $this->validation = $date;
    }

    public function isPapier() {
        return $this->exist('papier') && $this->get('papier');
    }

    public function isValide() {
      return ($this->validation);
    }

    public function isApprouve() {
      return ($this->validation_odg);
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

    public function getMvtLots() {
      $lots = array();
      foreach (MouvementLotView::getInstance()->getByDeclarantIdentifiant($this->identifiant)->rows as $item) {
          $key = Lot::generateMvtKey($item->value);
          if (preg_replace('/-.*/', '', $item->value->id_document) == ChgtDenomClient::ORIGINE_LOT) {
            $lots[$key] = $item->value;
          }
      }
      return $lots;
    }

    public function hasLots() {
      return !empty($this->__get('changement_origine_mvtkey'));
    }

    public function getLotKey() {
      return ($this->hasLots())? $this->changement_origine_mvtkey : null;
    }

    public function getMvtLot() {
      $mvts = $this->getMvtLots();
      $key = $this->getLotKey();
      return ($mvts && $key && isset($mvts[$key]))? $mvts[$key] : null;
    }

	  protected function doSave() {
      $this->piece_document->generatePieces();
	  }

    public function clearMouvementsLots(){
      $this->remove('mouvements_lots');
      $this->add('mouvements_lots');
    }

    public function clearLots(){
      $this->remove('lots');
      $this->add('lots');
    }

    public function isDeclassement() {
      return (!$this->changement_produit);
    }
    public function isChgtTotal() {
      return ($this->changement_volume == $this->getMvtLot()->volume);
    }

    public function getPourcentagesCepages() {
      $total = 0;
      $cepages = array();
      foreach($this->changement_cepages as $pc) {
        $total += $pc;
      }
      foreach($this->changement_cepages as $cep => $pc) {
        $cepages[$cep] += round(($pc/$total) * 100);
      }
      return $cepages;
    }

    public function generateLots() {
      $mvtLot = $this->getMvtLot();
      $this->clearMouvementsLots();
      $this->clearLots();

      $lots = array();
      $lot = MouvementLotView::generateLotByMvt($mvtLot);
      $lot->numero .= 'a';

      if (!$this->isChgtTotal()) {
        $lot->volume -= $this->changement_volume;
        $lotBis = MouvementLotView::generateLotByMvt($mvtLot);
        $lotBis->numero .= 'b';
        $lotBis->volume = $this->changement_volume;
        $lotBis->produit_hash = ($this->isDeclassement())? null : $this->changement_produit;
        $lotBis->produit_libelle = ($this->isDeclassement())? 'Déclassement' : $this->changement_produit_libelle;
        $lotBis->details = '';
        foreach($this->getPourcentagesCepages() as $cep => $pc) {
            $lotBis->details .= $cep.' ('.$pc.'%) ';
        }
        $lots[] = $lot;
        $lots[] = $lotBis;
      } else {
        $lot->produit_hash = ($this->isDeclassement())? null : $this->changement_produit;
        $lot->produit_libelle = ($this->isDeclassement())? 'Déclassement' : $this->changement_produit_libelle;
        if (count($this->changement_cepages->toArray(true, false))) {
          $lot->details = '';
          foreach($this->getPourcentagesCepages() as $cep => $pc) {
              $lotBis->details .= $cep.' ('.$pc.'%) ';
          }
        }
        $lots[] = $lot;
      }
      foreach($lots as $l) {
        $this->lots->add(null, $l);
      }
    }

    private function generateMouvementLotsFromLot($lot, $key, $prelevable = 1) {
        $mvt = new stdClass();
        $mvt->prelevable = ($lot->produit_hash)? $prelevable : 0;
        $mvt->preleve = 0;
        $mvt->date = $lot->date;
        $mvt->numero = $lot->numero;
        $mvt->millesime = $lot->millesime;
        $mvt->volume = $lot->volume;
        $mvt->elevage = $lot->elevage;
        $mvt->produit_hash = $lot->produit_hash;
        $mvt->produit_libelle = $lot->produit_libelle;
        $mvt->produit_couleur = ($lot->produit_hash)? $lot->getCouleurLibelle() : null;
        $mvt->region = null;
        $mvt->version = null;
        $mvt->origine_hash = $lot->getHash();
        $mvt->origine_type = 'chgtdenom';
        $mvt->origine_document_id = $this->_id;
        $mvt->id_document = $this->_id;
        $mvt->origine_mouvement = '/mouvements_lots/'.$this->identifiant.'/'.$key;
        $mvt->declarant_identifiant = $this->identifiant;
        $mvt->declarant_nom = $this->declarant->raison_sociale;
        $mvt->destination_type = $lot->destination_type;
        $mvt->destination_date = $lot->destination_date;
        $mvt->details = $lot->details;
        $mvt->campagne = $this->campagne;
        return $mvt;
    }

    private function generateAndAddMouvementLotsFromLot($lot, $key, $prelevable = 1) {
        $mvt = $this->generateMouvementLotsFromLot($lot, $key, $prelevable);
        if(!$this->add('mouvements_lots')->exist($this->identifiant)) {
            $this->add('mouvements_lots')->add($this->identifiant);
        }
        return $this->add('mouvements_lots')->get($this->identifiant)->add($key, $mvt);
    }

    public function generateMouvementsLots($prelevable = 1) {
        $origine = $this->getOrigineDocumentMvtLot();
        foreach($this->lots as $k => $lot) {
          $key = $lot->getUnicityKey();
          if ($key == $origine->getKey()) {
            $p = $origine->prelevable;
          } else {
            $p = $prelevable;
          }
          $mvt = $this->generateAndAddMouvementLotsFromLot($lot, $key, $p);
        }
        $this->updateMouvementOrigineDocument();
    }

    private function updateMouvementOrigineDocument() {
      if ($doc = $this->getOrigineDocumentMvtLot()) {
          $doc->prelevable = 0;
          $doc->getDocument()->save();
      }
    }

    public function getOrigineDocumentMvtLot() {
      $mvtLot = $this->getMvtLot();
      if ($doc = acCouchdbManager::getClient()->find($mvtLot->origine_document_id)) {
        if ($doc->exist($mvtLot->origine_mouvement)) {
          return $doc->get($mvtLot->origine_mouvement);
        }
      }
      return null;
    }

    public function getCepagesToStr(){
      $cepages = $this->changement_cepages;
      $str ='';
      $k=0;
      $total = 0.0;
      foreach ($cepages as $c => $volume){ $total+=$volume; }
      foreach ($cepages as $c => $volume){
        $k++;
        $p = ($total)? round(($volume/$total)*100) : 0.0;
        $str.= $c." (".$p.'%)';
        $str.= ($k < count($cepages))? ', ' : '';
      }
      return $str;
    }

    public function addCepage($cepage, $repartition) {
        $this->changement_cepages->add($cepage, $repartition);
    }

    public function getCepagesLibelle() {
        $libelle = null;
        foreach($this->changement_cepages as $cepage => $repartition) {
            if($libelle) {
                $libelle .= ", ";
            }
            $libelle .= $cepage . " (".$repartition."%)";
        }
        return $libelle;
    }

    /**** FIN DES MOUVEMENTS ****/

    /**** PIECES ****/
    public function getAllPieces() {
      $mvtLot = $this->getMvtLot();
      $libelle = ($this->isDeclassement())? 'Déclassement' : 'Changement de dénomination';
      $libelle .= ($this->isChgtTotal())? '' : ' partiel';
      $libelle .= ' du logement n°'.$mvtLot->numero;
      $libelle .= ($this->isPapier())? ' (Papier)' : ' (Télédéclaration)';
    	return (!$this->getValidation())? array() : array(array(
    		'identifiant' => $this->getIdentifiant(),
    		'date_depot' => $this->validation,
    		'libelle' => $libelle,
    		'mime' => Piece::MIME_PDF,
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
    	return sfContext::getInstance()->getRouting()->generate('chgtdenom_visualisation', array('id' => $id));
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
