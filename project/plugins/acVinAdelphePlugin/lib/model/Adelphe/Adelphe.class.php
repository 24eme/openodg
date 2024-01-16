<?php
/**
 * Model for Adelphe
 *
 */

class Adelphe extends BaseAdelphe implements InterfaceDeclarantDocument {

  protected $declarant_document = null;
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
  }

  public function constructId() {
    $id = AdelpheClient::TYPE_COUCHDB . '-' . $this->identifiant . '-' . $this->periode;
    $this->set('_id', $id);
  }

  public function initDoc($identifiant, $periode) {
    $this->identifiant = $identifiant;
    $this->periode = $periode;
    $this->constructId();
  }

  public function storeDeclarant() {
    $this->declarant_document->storeDeclarant();
    if ($famille = $this->getEtablissementObject()->famille) {
        $this->declarant->famille = $famille;
    }
  }

  public function isValideeOdg() {
    return boolval($this->getValidationOdg());
  }

  public function getEtablissementObject() {
    if($this->etablissement) {
      return $this->etablissement;
    }
    $this->etablissement = EtablissementClient::getInstance()->findByIdentifiant($this->identifiant);
    return $this->etablissement;
  }
}
