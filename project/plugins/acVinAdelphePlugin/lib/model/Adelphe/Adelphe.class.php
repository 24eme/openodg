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
    $id = AdelpheClient::TYPE_COUCHDB . '-' . $this->identifiant . '-' . $this->getPeriode();
    $this->set('_id', $id);
  }

  public function getPeriode() {
      return substr($this->campagne, 0, 4);
  }

  public function initDoc($identifiant, $periode) {
    $this->identifiant = $identifiant;
    $this->campagne = ConfigurationClient::getInstance()->buildCampagneFromYearOrCampagne($periode);
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

  public function storeEtape($etape) {
    $etapeOriginal = ($this->exist('etape')) ? $this->etape : null;
    $this->add('etape', $etape);
    return $etapeOriginal != $this->etape;
  }

  public function validate($date = null) {
      if(is_null($date)) {
          $date = date('c');
      }
      $this->validation = $date;
  }

  public function getTauxBibCalcule() {
    if ($this->volume_conditionne_bib > 0 && $this->volume_conditionne_total > 0 && $this->volume_conditionne_total >= $this->volume_conditionne_bib) {
      return round($this->volume_conditionne_bib / $this->volume_conditionne_total * 100);
    }
    return null;
  }

  public function conditionnementUniquementBouteille() {
    $this->conditionnement_bib = 0;
    $this->repartition_bib = 0;
    $this->volume_conditionne_bib = 0;
    $this->volume_conditionne_bouteille = $this->volume_conditionne_total;
  }

  public function conditionnementBibForfait() {
    $this->conditionnement_bib = 1;
    $this->repartition_bib = 0;
    $this->volume_conditionne_bib = round($this->volume_conditionne_total * AdelpheConfiguration::getInstance()->getTauxForfaitaireBib());
    $this->volume_conditionne_bouteille = $this->volume_conditionne_total - $this->volume_conditionne_bib;
  }
}
