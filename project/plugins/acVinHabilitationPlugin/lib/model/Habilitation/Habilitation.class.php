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
        if ($this->_id == $this->getTheoriticalId()) {
          throw new sfException("La date de l'habilitation doit être différente de celle du document d'origine");
        }
        parent::__clone();
        $this->initDocuments();
        $this->constructId();
    }



    protected function initDocuments() {
        $this->declarant_document = new DeclarantDocument($this);
        $this->historique = array();
    }

    private function getTheoriticalId() {
      $date = str_ireplace("-","",$this->date);
      return 'HABILITATION-' . $this->identifiant. '-'. $date;
    }

    public function constructId() {
        $id = $this->getTheoriticalId();
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
        $this->addHistoriqueNewProduit($produit_libelle);
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

  private function addHistoriqueNewProduit($complement){
      $this->addHistorique("Ajout du produit : ".$complement);
  }

  public function addHistorique($description, $commentaire = '', $auteur = '') {
    $historiqueRow = $this->get('historique')->add(null);
    $historiqueRow->iddoc = $this->_id;
    $historiqueRow->date = $this->getDate();
    if (!$auteur && sfContext::getInstance()->getUser() && sfContext::getInstance()->getUser()->getCompte()) {
      $historiqueRow->auteur = (sfContext::getInstance()->getUser()->isAdmin())? 'Admin' : sfContext::getInstance()->getUser()->getCompte()->identifiant;
    }else{
      $historiqueRow->auteur = $auteur;
    }
    $historiqueRow->description = $description;
    $historiqueRow->commentaire = $commentaire;

  }

  public function getFullHistoriqueReverse(){
    $historiqueReverse = $this->getFullHistorique();
    $historiqueReverse = array_reverse($historiqueReverse);
    return $historiqueReverse;
  }

  public function getFullHistorique() {
    $historique = array();
    foreach (HabilitationClient::getInstance()->getHistory($this->identifiant, $hydrate = acCouchdbClient::HYDRATE_JSON) as $hab) {
      $historique = array_merge($historique, $hab->historique);
    }
      return $historique;
  }

}
