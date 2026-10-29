<?php

/**
 * Model for DRev
 *
 */
class DRevMarc extends BaseDRevMarc implements InterfaceDeclarantDocument, InterfaceDeclaration, InterfaceMouvementFacturesDocument, InterfacePieceDocument {

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
        $this->mouvement_document = new MouvementFacturesDocument($this);
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

    public function devalidate() {
        $this->validation = null;
        $this->validation_odg = null;
        $this->remove('etape');
        $this->add('etape');

        $this->remove('mouvements');
        $this->add('mouvements');
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

    public function validateOdg($date = null) {
        if (!$date) {
            $date = date('Y-m-d');
        }
        $this->validation_odg = $date;
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

    public function getMouvementsFactures() {

        return $this->_get('mouvements');
    }

    public function getMouvementsFacturesCalcule() {
        $templateFacture = $this->getTemplateFacture();

        if(!$templateFacture) {
            return array();
        }

        $cotisations = $templateFacture->generateCotisations($this);

        $identifiantCompte = "E".$this->getIdentifiant();

        $mouvements = array();

        $rienAFacturer = true;

        foreach($cotisations as $cotisation) {
            $mouvement = DRevMarcMouvementFactures::freeInstance($this);
            $mouvement->categorie = $cotisation->getCollectionKey();
            $mouvement->type_hash = $cotisation->getDetailKey();
            $mouvement->type_libelle = $cotisation->getConfigCollection()->getLibelle();
            $mouvement->detail_libelle = $cotisation->getLibelle();
            $mouvement->quantite = $cotisation->getQuantite();
            $mouvement->taux = $cotisation->getPrix();
            $mouvement->tva = $cotisation->getTva();
            $mouvement->facture = 0;
            $mouvement->facturable = 1;
            $mouvement->date = $this->getCampagne()."-10-10";
            $mouvement->date_version = $this->validation;
            $mouvement->version = null;
            $mouvement->template = $templateFacture->_id;
            $mouvement->type = DRevMarcClient::TYPE_MODEL;

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

    public function getTemplateFacture() {

        return TemplateFactureClient::getInstance()->find("TEMPLATE-FACTURE-AOC-".$this->getCampagne());
    }

    public function getMouvementsFacturesCalculeByIdentifiant($identifiant) {

        return $this->mouvement_document->getMouvementsFacturesCalculeByIdentifiant($identifiant);
    }

    public function generateMouvementsFactures() {
        if(!$this->validation_odg) {

            return false;
        }

        if(!$this->getTemplateFacture()) {

            return false;
        }

        return $this->mouvement_document->generateMouvementsFactures();
    }

    public function findMouvementFactures($cle, $id = null){
      return $this->mouvement_document->findMouvementFactures($cle, $id);
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

    public function clearMouvementsFactures(){
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

    public static function getUrlGenerationCsvPiece($id, $admin = false) {
    	return null;
    }

    /**** FIN DES PIECES ****/

    public static function isVisualisationMasterUrl($admin = false) {

        return true;
    }

    public static function isPieceEditable($admin = false) {
    	return false;
    }

}
