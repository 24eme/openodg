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
    
    public function getNbFichier()
    {
    	return count($this->_attachments);
    }

    public function hasFichiers() {
    	return ($this->getNbFichier() > 0);
    }

    public function isMultiFichiers() {
    	return ($this->getNbFichier() > 1);
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

    public function getMime($file = null)
    {
    	if (!$file) {
    		foreach ($this->_attachments as $filename => $fileinfos) {
    			$file = $filename;
    		}
    	}
    	if ($file && $this->_attachments->exist($file)) {
    		$fileinfos = $this->_attachments->get($file)->toArray();
    		return $fileinfos['content_type'];
    	}
    	return null;
    }
    
    public function getFichiers()
    {
    	$fichiers = array();
    	foreach ($this->_attachments as $filename => $fileinfos) {
    		$fichiers[] = $filename;
    	}
    	return $fichiers;
    }
	
	protected function doSave() {
		$this->piece_document->generatePieces();
	}
	
	public function storeFichier($file) {
		if (!is_file($file)) {
			throw new sfException($file." n'est pas un fichier valide");
		}
		$infos = pathinfo($file);
		$extension = (isset($infos['extension']) && $infos['extension'])? strtolower($infos['extension']): null;
		$fileName = ($extension)? uniqid().'.'.$extension : uniqid();
		$mime = mime_content_type($file);
		$this->storeAttachment($file, $mime, $fileName);
	}
	
	public function deleteFichier($filename = null) {
		if (!$filename) {
			$this->remove('_attachments');
			$this->add('_attachments');
		} elseif ($this->_attachments->exist($filename)) {
			$this->_attachments->remove($filename);
		}
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
    		'visibilite' => $this->getVisibilite(),
    		'mime' => null,
    		'source' => null,
    		'fichiers' => $this->getFichiers()
    	));
    }

    public function generatePieces() {
    	return $this->piece_document->generatePieces();
    }

    public function generateUrlPiece($source = null) {
    	return sfContext::getInstance()->getRouting()->generate('get_fichier', $this);
    }

    public static function getUrlVisualisationPiece($id, $admin = false) {
		if(!$admin) {

			return null;
		}

		$fichier = FichierClient::getInstance()->find($id);

    	return sfContext::getInstance()->getRouting()->generate('upload_fichier', array('fichier_id' => $id, 'sf_subject' => $fichier->getEtablissementObject()));
    }

    /**** FIN DES PIECES ****/

}
