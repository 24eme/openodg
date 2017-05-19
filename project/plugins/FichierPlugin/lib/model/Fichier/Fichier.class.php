<?php
/**
 * Model for Fichier
 *
 */

class Fichier extends BaseFichier implements InterfacePieceDocument {
	
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

    public function constructId() {
        $this->set('_id', 'FICHIER-' . $this->identifiant . '-' . $this->fichier_id);
    }

    public function hasAttachments() {
    	return (count($this->_attachments) > 0);
    }
    
    public function initDoc($identifiant) {
    	$this->identifiant = $identifiant;
    	$this->fichier_id = uniqid();
    	$this->date_depot = date('Y-m-d');
    	$this->visibilite = 1;
    }

    public function getEtablissementObject() {
    
    	return EtablissementClient::getInstance()->findByIdentifiant($this->identifiant);
    }

    public function isPapier() {
    
    	return $this->exist('papier') && $this->get('papier');
    }
	
	protected function doSave() {
		$this->piece_document->generatePieces();
	}
	
	public function storeFichier($file) {
		$this->remove('_attachments');
		$this->add('_attachments');
		if (!is_file($file)) {
			throw new sfException($file." n'est pas un fichier valide");
		}
		$infos = pathinfo($file);
		$extension = (isset($infos['extension']) && $infos['extension'])? strtolower($infos['extension']): null;
		$this->mime = mime_content_type($file);
		$this->storeAttachment($file, $this->mime, ($extension)? $this->fichier_id.'.'.$extension : $this->fichier_id);
	}
	
	public function getDateDepotFormat($format = 'd/m/Y') {
		if ($this->date_depot) {
			$date = new DateTime($this->date_depot);
			return $date->format($format);
		}
		return null;
	}
    
    /**** PIECES ****/

    public function getAllPieces() {
    	$complement = ($this->isPapier())? '(Papier)' : '(Télédéclaration)';
    	return array(array(
    		'identifiant' => $this->getIdentifiant(),
    		'date_depot' => $this->getDateDepot(),
    		'libelle' => $this->getLibelle().' '.$complement,
    		'mime' => $this->getMime(),
    		'visibilite' => $this->getVisibilite(),
    		'source' => null
    	));
    }
    
    public function generatePieces() {
    	return $this->piece_document->generatePieces();
    }
    
    public function generateUrlPiece($source = null) {
    	return sfContext::getInstance()->getRouting()->generate('get_fichier', $this);
    }

    public static function getUrlVisualisationPiece($id, $admin = false) {
    	return ($admin)? sfContext::getInstance()->getRouting()->generate('upload_fichier', array('fichier_id' => $id)) : null;
    }
    
    /**** FIN DES PIECES ****/

}