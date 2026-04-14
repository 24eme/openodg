<?php

/**
 * Model for DRev
 *
 */
class Courrier extends BaseCourrier implements InterfaceDeclarantDocument, InterfacePieceDocument, InterfaceMouvementLotsDocument {

    protected $declarant_document = null;
    protected $piece_document = null;
    protected $etablissement = null;

    private $arg_courrier_type = null;
    private $arg_lot_origine = null;

    public function __construct(Lot $lot_origine = null, $courrier_type = null) {
        if ($lot_origine) {
            $this->arg_lot_origine = $lot_origine;
        }
        if ($courrier_type) {
            $this->arg_courrier_type = $courrier_type;
        }
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

    public function initDoc($identifiant, $campagne, $date = null) {
        if (!$date) {
            $date = date('Y-m-d h:m:s');
        }
        if (strlen($date) == 10) {
            $date .= ' 00:00:00';
        }
        $this->identifiant = $identifiant;
        $this->date = $date;

        $this->courrier_type = $this->arg_courrier_type;
        $this->courrier_titre = CourrierClient::getInstance()->getTitre($this->courrier_type);

        $etablissement = $this->getEtablissementObject();
        $this->constructId();
        $this->lots->add(0);
        $this->arg_lot_origine->remove('numero_anonymat');
        $this->arg_lot_origine->remove('numero_table');
        $this->arg_lot_origine->remove('position');
        $this->arg_lot_origine->remove('leurre');
        $this->arg_lot_origine->remove('prelevement_heure');
        $this->lots[0] = clone $this->arg_lot_origine;
        $this->lots[0]->id_document_provenance = $this->arg_lot_origine->id_document;
        $this->lots[0]->id_document = $this->_id;
        $this->lots[0]->document_ordre = sprintf('%02d', $this->arg_lot_origine->document_ordre + 1);

        $this->numero_dossier = $this->getNumeroDossier();
        $this->numero_archive = $this->getNumeroArchive();
        $this->add('region');
        $this->region = Organisme::getOIRegion();
    }

    public function getDegustation() {
        return $this->getMother();
    }

    public function constructId() {
        if (strlen($this->date) == 10) {
            $this->date .= ' 00:00:00';
        }
        $date = substr(str_replace(['-', ' ', ':'], '', $this->date), 0, 8);
        $id = 'COURRIER-' . $this->identifiant . '-' . $this->arg_lot_origine->unique_id . '-' . $date;
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

    protected function doSave() {
        return ;
    }

    public function save($saveDependants = true) {
        $this->lot->updateDocumentDependances();
        $this->generateMouvementsLots();
        $this->generatePieces();
        $ret = parent::save($saveDependants);
        if ($saveDependants) {
            $this->saveDocumentsDependants();
        }
        return $ret;
    }

    public function getMother() {
        return ($this->lots[0]->id_document_provenance) ? DeclarationClient::getInstance()->find($this->lots[0]->id_document_provenance) : null ;
    }

    public function getSecteur() {
        return null;
    }

    /**** PIECES ****/

    public function getAllPieces() {
    	return array(array(
    		'identifiant' => $this->getIdentifiant(),
    		'date_depot' => $this->date,
    		'libelle' => 'Courrier de notification '.$this->courrier_titre,
    		'mime' => Piece::MIME_PDF,
    		'visibilite' => 1,
    		'source' => null
    	));
    }

    public function generatePieces() {
    	return $this->piece_document->generatePieces();
    }

    public function generateUrlPiece($source = null) {
    	return sfContext::getInstance()->getRouting()->generate('courrier_visualisation', array('id' => $this->_id));
    }

    public static function getUrlVisualisationPiece($id, $admin = false) {
    	return sfContext::getInstance()->getRouting()->generate('courrier_visualisation', array('id' => $id));
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
        if(!$this->lots->exist(0)) {
            return;
        }
        $this->addMouvementLot($this->lots[0]->buildMouvement(Lot::STATUT_NOTIFICATION_COURRIER, $this->courrier_titre));

        if ($this->lots[0]->isAffectable()) {
            $this->addMouvementLot($this->lots[0]->buildMouvement(Lot::STATUT_AFFECTABLE, Lot::generateTextePassageMouvement($this->lots[0]->getNombrePassage() + 1)));
        }

        if ($this->lots[0]->exist('recours_oc') && $this->lots[0]->recours_oc) {
            $this->addMouvementLot($this->lots[0]->buildMouvement(Lot::STATUT_RECOURS_OC, null, $this->lots[0]->recours_oc));
            $this->lots[0]->statut = Lot::STATUT_NONCONFORME;
        }

        if(in_array($this->lots[0]->statut, array(Lot::STATUT_NONCONFORME, Lot::STATUT_RECOURS_OC)) && !$this->lots[0]->id_document_affectation) {
            $this->addMouvementLot($this->lots[0]->buildMouvement(Lot::STATUT_MANQUEMENT_EN_ATTENTE));
        }
    }

    public function getLot($lot_unique_id = null) {
        if ($lot_unique_id && ($this->lots[0]->unique_id != $lot_unique_id) ) {
            throw new sfException('Lot '.$lot_unique_id.' pas dans le courrier '.$this->_id);
        }
        return $this->lots[0];
    }

    public function getPDFTemplateNameForPageId($i) {
        return CourrierClient::getInstance()->getPDFTemplateNameForPageId($this->courrier_type, $i);
    }

    public function getNbPages() {
        return CourrierClient::getInstance()->getNbPages($this->courrier_type);
    }

    public function getDateFormat($format = 'Y-m-d') {
        if (!$this->date) {
            return date($format);
        }
        return date ($format, strtotime($this->date));
    }

    public function getNumeroDossier()
    {
        return "A definir";
    }

    public function getNumeroArchive()
    {
        return "A definir";
    }

    public function getExtraDateFormat($k, $format = 'd/m/Y') {
        if ($this->exist("extras") == false) {
            return '';
        }
        if (!$this->extras->exist($k)) {
            return;
        }
        return date($format, strtotime($this->extras->get($k)));
    }

    public function getExtra($k) {
        if ($this->exist("extras") == false) {
            return '';
        }
        if ($this->extras->exist($k)) {
            return $this->extras->get($k);
        }
        return '';
    }

    public function isFactures() {

        return false;
    }

}
