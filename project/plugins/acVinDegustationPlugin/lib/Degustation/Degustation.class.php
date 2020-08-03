<?php
/**
 * Model for Degustation
 *
 */

class Degustation extends BaseDegustation implements InterfacePieceDocument {

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
        $this->piece_document = new PieceDocument($this);
    }

    public function getConfiguration() {

        return acCouchdbManager::getClient('Configuration')->retrieveConfiguration($this->getCampagne());
    }

    public function getCampagne() {

        return $this->millesime;
    }

    public function constructId() {
        $id = sprintf("%s-%s-%s", DegustationClient::TYPE_COUCHDB, $this->identifiant, str_replace("-", "", $this->date));

        $this->set('_id', $id);
    }

    public function getEtablissementObject() {

        return EtablissementClient::getInstance()->find("ETABLISSEMENT-".$this->identifiant);
    }

	protected function doSave() {
		$this->piece_document->generatePieces();
	}

    /**** PIECES ****/

    public function getAllPieces() {
    	$pieces = array();
    	foreach ($this->prelevements as $key => $prelevement) {
    		if ($prelevement->exist('type_courrier') && $prelevement->type_courrier) {
	    		if (!$this->getDateDegustation()) { continue; }
	    		$pieces[] = array(
	    			'identifiant' => $this->getIdentifiant(),
	    			'date_depot' => $this->getDateDegustation(),
	    			'libelle' => 'Dégustation conseil '.$this->getMillesime().' '.$prelevement->getLibelleProduit().' ('.$prelevement->getLibelle().')',
	    			'mime' => Piece::MIME_PDF,
	    			'visibilite' => 1,
	    			'source' => $key
	    		);
    		}
    	}
    	return $pieces;
    }

    public function generatePieces() {
    	return $this->piece_document->generatePieces();
    }

    public function generateUrlPiece($source = null) {
    	return sfContext::getInstance()->getRouting()->generate('degustation_courrier_prelevement', $this->prelevements->get($source));
    }

    public static function getUrlVisualisationPiece($id, $admin = false) {
    	return ($admin)? sfContext::getInstance()->getRouting()->generate('degustation_visualisation', array('id' => preg_replace('/DEGUSTATION-[a-zA-Z0-9]*-/', 'TOURNEE-', $id))) : null;
    }

    public static function getUrlGenerationCsvPiece($id, $admin = false) {
    	return null;
    }

    public static function isVisualisationMasterUrl($admin = false) {
    	return false;
    }

    public static function isPieceEditable($admin = false) {
    	return false;
    }

    /**** FIN DES PIECES ****/
}
