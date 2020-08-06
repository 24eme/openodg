<?php
/**
 * Model for Degustation
 *
 */

class Degustation extends BaseDegustation implements InterfacePieceDocument, InterfaceMouvementLotsDocument {

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

        return substr($this->date, 0, 4);
    }

    public function constructId() {
        $id = sprintf("%s-%s-%s", DegustationClient::TYPE_COUCHDB, str_replace("-", "", $this->date), $this->getLieuNom(true));

        $this->set('_id', $id);
    }
    
    public function getLieuNom($slugify = false) {
        return self::getNomByLieu($this->lieu, $slugify);
    }
    
    public static function getNomByLieu($lieu, $slugify = false) {
        if (strpos($lieu, "—") === false) {
            throw new sfException('Le lieu « '.$lieu.' » n\'est pas correctement formaté dans la configuration. Séparateur « — » non trouvé.');
        }
        $lieuExpld = explode('—', $lieu);
        return ($slugify)? KeyInflector::slugify($lieuExpld[0]) : $lieuExpld[0];
    }

    public function getEtablissementObject() {

        return EtablissementClient::getInstance()->find("ETABLISSEMENT-".$this->identifiant);
    }

	protected function doSave() {
		$this->piece_document->generatePieces();
	}

	public function storeEtape($etape) {
	    if ($etape == $this->etape) {
	
	        return false;
	    }
	
	    $this->add('etape', $etape);
	
	    return true;
	}

	public function validate($date = null) {
	    if(is_null($date)) {
	        $date = date('Y-m-d');
	    }
	    $this->updateMouvementsLots();
	    $this->generateMouvementsLots();
	}

	public function updateMouvementsLots() {
	    foreach ($this->lots as $lot) {
	        $doc = acCouchdbManager::getClient()->find($lot->id_document);
	        if ($doc instanceof InterfaceMouvementLotsDocument) {
	            if ($doc->exist('identifiant') && $doc->mouvements_lots->exist($doc->identifiant) && $doc->mouvements_lots->get($doc->identifiant)->exist($lot->getGenerateKey())) {
	               $doc->mouvements_lots->get($doc->identifiant)->get($lot->getGenerateKey())->set('preleve', 1);
	               $doc->save();
	            }
	        }
	    }
	}
	
	public function generateMouvementsLots() {
	    // A implementer lorsque les lots devront etre redegustes
	}

    /**** PIECES ****/

    public function getAllPieces() {
    	$pieces = array();
    	return $pieces;
    }

    public function generatePieces() {
    	return $this->piece_document->generatePieces();
    }

    public function generateUrlPiece($source = null) {
    	return null;
    }

    public static function getUrlVisualisationPiece($id, $admin = false) {
    	return null;
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
