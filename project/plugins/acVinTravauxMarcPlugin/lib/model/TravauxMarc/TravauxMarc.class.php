<?php
/**
 * Model for TravauxMarc
 *
 */

class TravauxMarc extends BaseTravauxMarc implements InterfaceDeclarantDocument, InterfaceDeclaration, InterfacePieceDocument {

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
        $this->set('_id', TravauxMarcClient::TYPE_COUCHDB.'-' . $this->identifiant . '-' . $this->campagne);
    }

    public function initDoc($identifiant, $campagne) {
        $this->identifiant = $identifiant;
        $this->campagne = $campagne;
    }

    public function storeDeclarant() {
        $this->declarant_document->storeDeclarant();
    }

    public function storeEtape($etape) {
    	$this->add('etape', $etape);
    }

    public function getEtablissementObject() {

        return EtablissementClient::getInstance()->findByIdentifiant($this->identifiant);
    }

    public function validate($date = null) {
        if(is_null($date)) {
            $date = date('Y-m-d');
        }

        $this->validation = $date;
    }

    public function devalidate() {
        $this->validation = null;
        $this->validation_odg = null;
        $this->remove('etape');
        $this->add('etape');
    }

    public function isValide() {
        return $this->exist('validation') && $this->validation;
    }

    public function isPapier() {

        return $this->exist('papier') && $this->get('papier');
    }

    public function isLectureSeule() {

        return $this->exist('lecture_seule') && $this->get('lecture_seule');
    }

    public function isAutomatique() {

        return $this->exist('automatique') && $this->get('automatique');
    }

    public function getValidation() {

        return $this->_get('validation');
    }

    public function getValidationOdg() {

        return $this->_get('validation_odg');
    }

    public function validateOdg() {
        $this->validation_odg = date('Y-m-d');
    }

    /**** PIECES ****/

    protected function doSave() {
        $this->piece_document->generatePieces();
    }

    public function getAllPieces() {
    	$complement = ($this->isPapier())? '(Papier)' : '(Télédéclaration)';
    	return (!$this->getValidation())? array() : array(array(
    		'identifiant' => $this->getIdentifiant(),
    		'date_depot' => $this->getValidation(),
    		'libelle' => "Déclaration d'ouverture des travaux de distillation ".$this->campagne.' '.$complement,
    		'mime' => Piece::MIME_PDF,
    		'visibilite' => 1,
    		'source' => null
    	));
    }

    public function generatePieces() {
    	return $this->piece_document->generatePieces();
    }

    public function generateUrlPiece($source = null) {
    	return sfContext::getInstance()->getRouting()->generate('drevmarc_export_pdf', $this);
    }

    public static function getUrlVisualisationPiece($id, $admin = false) {
    	return sfContext::getInstance()->getRouting()->generate('drevmarc_visualisation', array('id' => $id));
    }

    /**** FIN DES PIECES ****/

    public function getDateDistillationFr() {
        $date = $this->getDateDistillationObject();

        if (!$date) {

            return null;
        }

        return $date->format('d/m/Y');
    }

    public function getDateDistillationObject() {
        if (!$this->date_distillation) {

            return null;
        }

        return new DateTime($this->date_distillation);
    }
}
