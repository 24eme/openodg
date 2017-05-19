<?php

/**
 * Model for DRev
 *
 */
class DRevMarc extends BaseDRevMarc implements InterfaceDeclarantDocument, InterfaceDeclaration, InterfaceMouvementDocument, InterfacePieceDocument {

    protected $declarant_document = null;
    protected $mouvement_document = null;
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
        $this->mouvement_document = new MouvementDocument($this);
        $this->piece_document = new PieceDocument($this);
    }

    public function constructId() {
        $this->set('_id', 'DREVMARC-' . $this->identifiant . '-' . $this->campagne);
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

    /*
     * Facture
     */
    public function getVolumeFacturable()
    {
        return $this->volume_obtenu;
    }
    
    protected function doSave() {
    	$this->piece_document->generatePieces();
    }

    /**** MOUVEMENTS ****/

    public function getMouvements() {

        return $this->_get('mouvements');
    }

    public function getMouvementsCalcule() {

        return array("E".$this->getIdentifiant() => array("TEMPLATE-FACTURE-MARC-".$this->campagne => array("facturable" => 1, "facture" => 0)));
    }

    public function getMouvementsCalculeByIdentifiant($identifiant) {

        return $this->mouvement_document->getMouvementsCalculeByIdentifiant($identifiant);
    }

    public function generateMouvements() {

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
    
    /**** PIECES ****/

    public function getAllPieces() {
    	$complement = ($this->isPapier())? '(Papier)' : '(Télédéclaration)';
    	return (!$this->getValidation())? array() : array(array(
    		'identifiant' => $this->getIdentifiant(),
    		'date_depot' => $this->getValidation(),
    		'libelle' => 'Revendication de Marc d\'Alsace Gewurztraminer '.$this->campagne.' '.$complement,
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
}
