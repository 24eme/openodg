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
        $templateFacture = $this->getTemplateFacture();

        if(!$templateFacture) {
            return array();
        }

        $cotisations = $templateFacture->generateCotisations($this);

        $identifiantCompte = "E".$this->getIdentifiant();

        $mouvements = array();

        $rienAFacturer = true;

        foreach($cotisations as $cotisation) {
            $mouvement = DRevMarcMouvement::freeInstance($this);
            $mouvement->categorie = $cotisation->getCollectionKey();
            $mouvement->type_hash = $cotisation->getDetailKey();
            $mouvement->type_libelle = $cotisation->getLibelle();
            $mouvement->quantite = $cotisation->getQuantite();
            $mouvement->taux = $cotisation->getPrix();
            $mouvement->facture = 0;
            $mouvement->facturable = 1;
            $mouvement->date = $this->getCampagne()."110-10";
            $mouvement->date_version = $this->validation;
            $mouvement->version = null;
            $mouvement->template = $templateFacture->_id;

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

        return TemplateFactureClient::getInstance()->find("TEMPLATE-FACTURE-MARC-".$this->getCampagne());
    }

    public function getMouvementsCalculeByIdentifiant($identifiant) {

        return $this->mouvement_document->getMouvementsCalculeByIdentifiant($identifiant);
    }

    public function generateMouvements() {
        if(!$this->validation_odg) {

            return false;
        }

        if(!$this->getTemplateFacture()) {

            return false;
        }

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
