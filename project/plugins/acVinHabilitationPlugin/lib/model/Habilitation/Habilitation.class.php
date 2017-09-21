<?php
/**
 * Model for Habilitation
 *
 */

class Habilitation extends BaseHabilitation implements InterfaceProduitsDocument, InterfaceVersionDocument, InterfaceDeclarantDocument, InterfaceDeclaration, InterfaceMouvementDocument {



    protected $declarant_document = null;
    protected $mouvement_document = null;
    protected $version_document = null;

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
        $this->version_document = new VersionDocument($this);
    }

    public function constructId() {
        $date = str_ireplace("-","",$this->date);
        $id = 'HABILITATION-' . $this->identifiant. '-'. $date;
        if($this->version) {
            $id .= "-".$this->version;
        }
        $this->set('_id', $id);
    }

    public function getConfiguration() {

        return acCouchdbManager::getClient('Configuration')->retrieveConfiguration();
    }

    public function getProduits($onlyActive = true) {
        return $this->declaration->getProduits($onlyActive);
    }

    public function isPapier() {

        return $this->exist('papier') && $this->get('papier');
    }

    public function isAutomatique() {

        return $this->exist('automatique') && $this->get('automatique');
    }

    public function isLectureSeule() {

        return $this->exist('lecture_seule') && $this->get('lecture_seule');
    }

    public function getValidation() {

        return $this->_get('validation');
    }

    public function getValidationOdg() {

        return $this->_get('validation_odg');
    }

    public function initDoc($identifiant,$date) {
        $this->identifiant = $identifiant;
        $this->date = $date;
        $etablissement = $this->getEtablissementObject();
    }

    public function addProduitCepage($hash) {
        $produit = $this->getOrAdd($hash);
        $this->addProduit($produit->getHash());
        return $produit->getOrAddDetailNode();
    }

    public function addProduit($hash) {
        $config = $this->getConfiguration()->get($hash);
        $produit = $this->getOrAdd($config->getHash());
        $produit->getLibelle();
        return $produit;
    }

    public function storeDeclarant() {
        $this->declarant_document->storeDeclarant();
    }

    public function storeEtape($etape) {
        $etapeOriginal = ($this->exist('etape')) ? $this->etape : null;

        $this->add('etape', $etape);

        return $etapeOriginal != $this->etape;
    }

    public function validate($date = null) {
        if(is_null($date)) {
            $date = date('Y-m-d');
        }

        $this->cleanDoc();
        $this->validation = $date;
        $this->generateMouvements();
    }


   public function getEtablissementObject() {

        return EtablissementClient::getInstance()->findByIdentifiant($this->identifiant);
    }



	protected function doSave() {

	}


    /**** MOUVEMENTS ****/

    public function getMouvements() {

        return $this->_get('mouvements');
    }

    public function getMouvementsCalcule() {
        $mouvements = array();

        foreach($this->declaration->getProduits() as $produit) {
            $types_hash = array(
                "volume_revendique" => "Volume revendiqué",
                "superficie_revendique" => "Superficie revendiqué",
                "superficie_vinifiee" => "Superficie vinifiée"
            );

            foreach($types_hash as $type_hash => $libelle) {
                $mouvement = $this->createMouvementByProduitAndType($produit, $type_hash, $libelle);
                if(!$mouvement) {

                    continue;
                }
                $mouvements[$this->getDocument()->getIdentifiant()][$mouvement->getMD5Key()] = $mouvement;
            }
        }

        return $mouvements;
    }

    public function createMouvementByProduitAndType($produit, $type_hash, $type_libelle) {
        $quantite = $produit->get($type_hash);

        if ($this->getDocument()->hasVersion() && $this->getDocument()->motherExist($produit->getHash() . '/' . $type_hash)) {
            $quantite = $quantite - $this->getDocument()->motherGet($produit->getHash() . '/' . $type_hash);
        }

        if (!$quantite) {

            return null;
        }

        $mouvement = HabilitationMouvement::freeInstance($this->getDocument());
        $mouvement->facture = 0;
        $mouvement->facturable = 1;
        $mouvement->produit_libelle = $produit->getLibelleComplet();
        $mouvement->produit_hash = $produit->getHash();
        $mouvement->type_hash = $type_hash;
        $mouvement->type_libelle = $type_libelle;
        $mouvement->quantite = $quantite;
        $mouvement->version = $this->getDocument()->getVersion();
        $mouvement->date = ($this->getDocument()->validation) ? ($this->getDocument()->validation) : date('Y-m-d');
        $mouvement->date_version = $mouvement->date;

        return $mouvement;
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


    /**** VERSION ****/

    public static function buildVersion($rectificative, $modificative) {

        return VersionDocument::buildVersion($rectificative, $modificative);
    }

    public static function buildRectificative($version) {

        return VersionDocument::buildRectificative($version);
    }

    public static function buildModificative($version) {

        return VersionDocument::buildModificative($version);
    }

    public function getVersion() {

        return $this->_get('version');
    }

    public function hasVersion() {

        return $this->version_document->hasVersion();
    }

    public function isVersionnable() {
        if (!$this->validation) {

            return false;
        }

        return $this->version_document->isVersionnable();
    }

    public function getRectificative() {

        return $this->version_document->getRectificative();
    }

    public function isRectificative() {

        return $this->version_document->isRectificative();
    }

    public function isRectifiable() {

        return false;
    }

    public function getModificative() {

        return $this->version_document->getModificative();
    }

    public function isModificative() {

        return $this->version_document->isModificative();
    }

    public function isModifiable() {
        return $this->version_document->isModifiable();
    }

    public function getPreviousVersion() {

        return $this->version_document->getPreviousVersion();
    }

    public function getMasterVersionOfRectificative() {

        throw new sfException("Not implemented");
    }

    public function needNextVersion() {

        return $this->version_document->needNextVersion() || !$this->isSuivanteCoherente();
    }

    public function getMaster() {

        return $this->version_document->getMaster();
    }

    public function isMaster() {

        return $this->version_document->isMaster();
    }

    public function findMaster() {

        return HabilitationClient::getInstance()->findMasterByIdentifiantAndCampagne($this->identifiant);
    }

    public function findDocumentByVersion($version) {
        $id = HabilitationClient::TYPE_COUCHDB.'-'.$this->identifiant;
        if($version) {
            $id .= "-".$this->version;
        }

        return HabilitationClient::getInstance()->find($id);
    }

    public function getMother() {

        return $this->version_document->getMother();
    }

    public function motherGet($hash) {

        return $this->version_document->motherGet($hash);
    }

    public function motherExist($hash) {

        return $this->version_document->motherExist($hash);
    }

    public function motherHasChanged() {
        if ($this->declaration->total != $this->getMother()->declaration->total) {

            return true;
        }

        if (count($this->getProduitsDetails($this->teledeclare)) != count($this->getMother()->getProduitsDetails($this->teledeclare))) {

            return true;
        }

        if ($this->droits->douane->getCumul() != $this->getMother()->droits->douane->getCumul()) {

            return true;
        }

        return false;
    }

    public function getDiffWithMother() {

        return $this->version_document->getDiffWithMother();
    }

    public function isModifiedMother($hash_or_object, $key = null) {

        return $this->version_document->isModifiedMother($hash_or_object, $key);
    }

    public function generateRectificative() {

        return $this->version_document->generateRectificative();
    }

    public function generateModificative() {
        $doc = $this->version_document->generateModificative();

        return $doc;
    }

    public function generateNextVersion() {

        throw new sfException("Not implemented");
    }

    public function listenerGenerateVersion($document) {
        $document->devalidate();
    }

    public function listenerGenerateNextVersion($document) {

    }

    public function getSuivante() {

        throw new sfException("Not implemented");
    }

    public function isValidee() {

        return $this->validation;
    }

    /**** FIN DE VERSION ****/
}
