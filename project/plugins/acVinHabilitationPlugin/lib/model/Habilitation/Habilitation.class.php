<?php
/**
 * Model for Habilitation
 *
 */

class Habilitation extends BaseHabilitation implements InterfaceProduitsDocument, InterfaceDeclarantDocument, InterfaceDeclaration {



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
        $this->date = date("Y-m-d");
        $this->constructId();
    }

    protected function initDocuments() {
        $this->declarant_document = new DeclarantDocument($this);
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

        return acCouchdbManager::getClient('Configuration')->getConfiguration();
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

    public function addProduit($hash) {
        $hashToAdd = preg_replace("|/declaration/|", '', $hash);
        $produit = $this->add('declaration')->add($hashToAdd);
        $produit_libelle = $produit->getLibelle();
        $produit->initActivites();
        $this->addHistorique(HabilitationHistorique::ADD_PRODUIT,$produit_libelle);
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

  public function isLastOne(){
    $last = HabilitationClient::getInstance()->getLastHabilitation($this->identifiant);
    return $this->_id == $last->_id;
  }

  protected function addHistorique($categorie,$complement){
      $historiqueRow = $this->get('historique')->add(null);
      $historiqueRow->iddoc = $this->_id;
      $historiqueRow->date = $this->getDate();
      $historiqueRow->auteur = (sfContext::getInstance()->getUser()->isAdmin())? 'Admin' : sfContext::getInstance()->getUser()->getCompte()->identifiant;
      $historiqueRow->description = HabilitationHistorique::$actionsDescriptions[$categorie]." : ".$complement;
  }

  public function getHistoriqueReverse(){
    $historiqueReverse = $this->getHistorique()->toArray(1,0);
    $historiqueReverse = array_reverse($historiqueReverse);
    return $historiqueReverse;
  }

}
