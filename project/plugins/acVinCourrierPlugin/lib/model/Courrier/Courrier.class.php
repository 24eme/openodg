<?php

/**
 * Model for DRev
 *
 */
class Courrier extends BaseCourrier implements InterfaceDeclarantDocument, InterfacePieceDocument, InterfaceMouvementLotsDocument {

    protected $declarant_document = null;
    protected $piece_document = null;
    protected $etablissement = null;

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

    public function initDoc($identifiant, $date, $type, $lot_origine) {
        $this->identifiant = $identifiant;
        $this->date = $date;

        $this->courrier_type = $type;
        $this->courrier_titre = CourrierClient::getInstance()->getTitre($type);

        $etablissement = $this->getEtablissementObject();
        $this->constructId();
        $this->lots->add(0);
        $lot_origine->remove('numero_anonymat');
        $lot_origine->remove('numero_table');
        $lot_origine->remove('position');
        $lot_origine->remove('leurre');
        $this->lots[0] = clone $lot_origine;
        $this->lots[0]->id_document_provenance = $lot_origine->id_document;
        $this->lots[0]->id_document = $this->_id;
        $this->lots[0]->document_ordre = sprintf('%02d', $lot_origine->document_ordre + 1);
        $this->numero_dossier = $this->lots[0]->numero_dossier;
        $this->numero_archive = $this->lots[0]->numero_archive;
        $this->add('region');
        $this->region = $this->lots[0]->region;
    }


    public function constructId() {
        $id = 'COURRIER-' . $this->identifiant . '-' . $this->date.'-'.$this->courrier_type;
        $this->set('_id', $id);
    }

    public function getConfiguration() {
        return ConfigurationClient::getInstance()->getConfiguration($this->date);
    }

    public function getConfigProduits() {

        return $this->getConfiguration()->declaration->getProduits();
    }

    public function isLectureSeule() {

        return $this->exist('lecture_seule') && $this->get('lecture_seule');
    }

    public function isPapier() {

        return $this->exist('papier') && $this->get('papier');
    }

    public function isAutomatique() {

        return $this->exist('automatique') && $this->get('automatique');
    }

    public function storeDeclarant() {
        $this->declarant_document->storeDeclarant();

        if($this->getEtablissementObject()->famille) {
            $this->declarant->famille = $this->getEtablissementObject()->famille;
        }
    }

    public function getEtablissementObject() {
        if($this->etablissement) {

            return $this->etablissement;
        }

        $this->etablissement = EtablissementClient::getInstance()->findByIdentifiant($this->identifiant);

        return $this->etablissement;
    }

    public function save($saveDependants = true) {
        $this->generateMouvementsLots();
        return parent::save();
    }

    /**** PIECES ****/

    public function getAllPieces() {
    	$complement = ($this->isPapier())? '(Papier)' : '(Télédéclaration)';
      $date = null;
      if ($this->getValidation()) {
        $dt = new DateTime($this->getValidation());
        $date = $dt->format('Y-m-d');
      }
    	return (!$this->getValidation())? array() : array(array(
    		'identifiant' => $this->getIdentifiant(),
    		'date_depot' => $date,
    		'libelle' => 'Revendication des produits '.$this->periode.' '.$complement,
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

    public function getCategorie(){
      return strtolower($this->type);
    }

    /**** FIN DES PIECES ****/

    public function clearMouvementsLots(){
        $this->remove('mouvements_lots');
        $this->add('mouvements_lots');
    }

    public function addMouvementLot($mouvement) {

        return $this->mouvements_lots->add($mouvement->declarant_identifiant)->add($mouvement->getUnicityKey(), $mouvement);
    }

    public function generateMouvementsLots()
    {
        $this->clearMouvementsLots();
        if(!count($this->lots->toArray(true, false))) {
            return;
        }
        $this->addMouvementLot($this->lots[0]->buildMouvement(Lot::STATUT_NOTIFICATION_COURRIER, $this->courrier_titre));
    }

    public function getLot($lot_unique_id) {
        if ($this->lots[0]->unique_id != $lot_unique_id) {
            throw new sfException('Lot '.$lot_unique_id.' pas dans le courrier '.$this->_id);
        }
        return $this->lots[0];
    }

}
