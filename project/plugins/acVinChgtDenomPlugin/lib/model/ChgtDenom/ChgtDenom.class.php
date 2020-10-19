<?php

class ChgtDenom extends ChgtDenomDRev implements InterfaceDeclarantDocument, InterfacePieceDocument, InterfaceMouvementLotsDocument {

    const DEFAULT_KEY = 'DEFAUT';

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

    public function constructId() {
        $id = 'CHGTDENOM-' . $this->identifiant . '-' . $this->date;
        $this->set('_id', $id);
    }

    public function getConfiguration() {
        $configuration = ConfigurationClient::getInstance()->getConfiguration($this->campagne.'-10-01');
        if(ConfigurationConfiguration::getInstance()->hasEffervescentVinbase()){
          $configuration->setEffervescentVindebaseActivate();
        }
        return $configuration;
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

    public function validateOdg($date = null, $region = null) {
        if(is_null($date)) {
            $date = date('Y-m-d');
        }

        if(!$region && DrevConfiguration::getInstance()->hasOdgProduits() && DrevConfiguration::getInstance()->hasValidationOdg()) {
            throw new sfException("La validation nécessite une région");
        }

        if(DrevConfiguration::getInstance()->hasOdgProduits() && $region){
            return $this->validateOdgByRegion($date, $region);
        }

        $this->validation_odg = $date;
        $this->generateMouvementsLots();
    }

    public function getEtablissementObject() {

        return EtablissementClient::getInstance()->findByIdentifiant($this->identifiant);
    }



	protected function doSave() {
        $this->piece_document->generatePieces();
	}


    public function hasLotUnicityKey($key) {
        foreach($this->lots as $k => $lot) {
            if ($lot->getUnicityKey() == $key) {
                return true;
            }
        }
        return false;
    }

    private function generateMouvementLotsFromLot($lot, $key, $prelevable = 1) {
        $mvt = new stdClass();
        $mvt->prelevable = $prelevable;
        $mvt->preleve = 0;
        $mvt->date = $lot->date;
        $mvt->numero = $lot->numero;
        $mvt->millesime = $lot->millesime;
        $mvt->volume = $lot->volume;
        $mvt->elevage = $lot->elevage;
        $mvt->produit_hash = $lot->produit_hash;
        $mvt->produit_libelle = $lot->produit_libelle;
        $mvt->produit_couleur = $lot->getCouleurLibelle();
        $mvt->region = '';
        $mvt->version = $this->getVersion();
        $mvt->origine_hash = $lot->getHash();
        $mvt->origine_type = 'drev';
        $mvt->origine_document_id = $this->_id;
        $mvt->id_document = $this->_id;
        $mvt->origine_mouvement = '/mouvements_lots/'.$this->identifiant.'/'.$key;
        $mvt->declarant_identifiant = $this->identifiant;
        $mvt->declarant_nom = $this->declarant->raison_sociale;
        $mvt->destination_type = $lot->destination_type;
        $mvt->destination_date = $lot->destination_date;
        $mvt->details = '';
        foreach($lot->cepages as $cep => $pc) {
            $mvt->details .= $cep.' ('.$pc.'%) ';
        }
        $mvt->region = '';
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

    public function generateMouvementsLots() {
        $prev = $this->getMother();
        foreach($this->lots as $k => $lot) {
            $key = $lot->getUnicityKey();
            if ($prev && $prev->hasLotUnicityKey($key)) {
                continue;
            }
            $mvt = $this->generateAndAddMouvementLotsFromLot($lot, $key);
        }
        if ($prev) {
            foreach($prev->lots as $k => $lot) {
                $key = $lot->getUnicityKey();
                if ($this->hasLotUnicityKey($key)) {
                    continue;
                }
                $this->generateAndAddMouvementLotsFromLot($lot, $key, 0);
            }
        }
    }



    public function clearMouvementsLots(){
        $this->remove('mouvements_lots');
        $this->add('mouvements_lots');
    }

    /**** FIN DES MOUVEMENTS ****/

    /**** PIECES ****/

    public function getAllPieces() {
    	$complement = ($this->isPapier())? '(Papier)' : '(Télédéclaration)';
    	$complement .= ($this->isSauvegarde())? ' Non facturé' : '';
    	return (!$this->getValidation())? array() : array(array(
    		'identifiant' => $this->getIdentifiant(),
    		'date_depot' => $this->getValidation(),
    		'libelle' => 'Changement de dénomination '.$this->date.' '.$complement,
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

    /**** FIN DES PIECES ****/
}
