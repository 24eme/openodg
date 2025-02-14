<?php
/**
 * Model for Adelphe
 *
 */

class Adelphe extends BaseAdelphe implements InterfaceDeclarantDocument, InterfaceDeclaration {

  protected $declarant_document = null;
  protected $etablissement = null;

  const VOL_COND_CSV_IDDV = 0;
  const VOL_COND_CSV_CVI = 1;
  const VOL_COND_CSV_SIRET = 2;
  const VOL_COND_CSV_ACCISES = 3;
  const VOL_COND_CSV_RS = 4;
  const VOL_COND_CSV_VOL = 5;

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
    $this->volume_conditionne_bib = 0;
    $this->volume_conditionne_bouteille = 0;
    $this->constructId();
    $this->storeDeclarant();
    $this->setVolumeConditionneTotalFromCsv();
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

  protected function preSave() {
    $this->updateCotisation();
  }

  public function validate($date = null) {
      if(is_null($date)) {
          $date = date('c');
      }
      $this->validation = $this->validation_odg = $date;
  }

  public function devalidate() {
    $this->validation = $this->validation_odg = null;
  }

  public function getTauxBibCalcule() {
    if ($this->volume_conditionne_bib > 0 && $this->volume_conditionne_total > 0 && $this->volume_conditionne_total >= $this->volume_conditionne_bib) {
      return round($this->volume_conditionne_bib / $this->volume_conditionne_total * 100);
    }
    return 0;
  }

  public function getTauxBouteilleCalcule() {
    return 100 - $this->getTauxBibCalcule();
  }

  public function getSeuil() {
    if (!AdelpheConfiguration::getInstance()->getFonctionCalculSeuil()) {
      return null;
    }
    $fctCalculSeuil = str_replace('%TXBIB%', $this->getTauxBibCalcule() / 100, AdelpheConfiguration::getInstance()->getFonctionCalculSeuil());
    if ($this->volume_conditionne_total) {
      return eval($fctCalculSeuil);
    }
    return null;
  }

  public function getMaxSeuil() {
      if (!AdelpheConfiguration::getInstance()->getFonctionCalculSeuil()) {
        return null;
      }
      $fctCalculSeuil = str_replace('%TXBIB%', '1', AdelpheConfiguration::getInstance()->getFonctionCalculSeuil());
      return eval($fctCalculSeuil);
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
    $this->volume_conditionne_bib = round($this->volume_conditionne_total * AdelpheConfiguration::getInstance()->getTauxForfaitaireBib(), 2);
    $this->volume_conditionne_bouteille = $this->volume_conditionne_total - $this->volume_conditionne_bib;
  }

  public function conditionnementBibReel() {
    $this->conditionnement_bib = 1;
    $this->repartition_bib = 1;
    $this->volume_conditionne_bouteille = $this->volume_conditionne_total - $this->volume_conditionne_bib;
  }

  public function setRedirect($input) {
      $this->redirect_adelphe = $input;
  }

  public function setVolumeConditionneTotalFromCsv() {
    $csvFile = AdelpheConfiguration::getInstance()->getVolumesConditionnesCsv($this->getPeriode());
    if (!file_exists($csvFile)) {
      throw new Exception('Le fichier '.$csvFile.' n\'existe pas');
    }
    if (($handle = fopen($csvFile, "r")) !== false) {
      while (($data = fgetcsv($handle, null, ";")) !== false) {
        if ($data[self::VOL_COND_CSV_CVI] && $data[self::VOL_COND_CSV_CVI] == $this->declarant->cvi) {
          $this->volume_conditionne_total = $data[self::VOL_COND_CSV_VOL];
          break;
        }
        if ($data[self::VOL_COND_CSV_SIRET] && $data[self::VOL_COND_CSV_SIRET] == $this->declarant->siret) {
          $this->volume_conditionne_total = $data[self::VOL_COND_CSV_VOL];
          break;
        }
      }
      fclose($handle);
    }
  }

  public function isRepartitionForfaitaire() {
    return ($this->conditionnement_bib && !$this->repartition_bib);
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

  public function getValidation() {
    return $this->_get('validation');
  }

  public function getValidationOdg() {
    return $this->_get('validation_odg');
  }

  public function updateCotisation() {
    /*
    * Valeurs prédéfinies par Adelphe
    */
    $partBouteilleNormale = 75/100;
    $partBouteilleAllegee = 25/100;
    $partCarton = 1/6;
    $prixUnitaireBouteilleNormale = 0.0105;
    $prixUnitaireBouteilleAllegee = 0.0090;
    $prixUnitaireCarton = 0.0535;
    $partBib3L = 55/100;
    $partBib5L = 35/100;
    $partBib10L = 10/100;
    $prixUnitaireBib3L = 0.0498;
    $prixUnitaireBib5L = 0.0617;
    $prixUnitaireBib10L = 0.1062;
    /*
    * Calcul des unités de conditionnement
    */
    $quantiteBouteilleNormale = round($this->volume_conditionne_bouteille * $partBouteilleNormale * 10000 / 75);
    $quantiteBouteilleAllegee = round($this->volume_conditionne_bouteille * $partBouteilleAllegee * 10000 / 75);
    $quantiteCarton = round(($quantiteBouteilleNormale + $quantiteBouteilleAllegee) * $partCarton);
    $quantiteBib3L = round($this->volume_conditionne_bib * $partBib3L * 100 / 3);
    $quantiteBib5L = round($this->volume_conditionne_bib * $partBib5L * 100 / 5);
    $quantiteBib10L = round($this->volume_conditionne_bib * $partBib10L * 100 / 10);
    /*
    * Calcul des prix
    */
    $prixBouteilleNormale = round($quantiteBouteilleNormale * $prixUnitaireBouteilleNormale);
    $prixBouteilleAllegee = round($quantiteBouteilleAllegee * $prixUnitaireBouteilleAllegee);
    $prixBouteilleCarton = round($quantiteCarton * $prixUnitaireCarton);
    $prixBib3L = round($quantiteBib3L * $prixUnitaireBib3L);
    $prixBib5L = round($quantiteBib5L * $prixUnitaireBib5L);
    $prixBib10L = round($quantiteBib10L * $prixUnitaireBib10L);
    $prixTotal = $prixBouteilleNormale + $prixBouteilleAllegee + $prixBouteilleCarton + $prixBib3L + $prixBib5L + $prixBib10L;
    /*
    * Controle du prix forfaitaire minimal
    */
    if ($prixTotal < 80) {
      $prixTotal = 80;
    }
    /*
    * Enregistrement en base de données
    */
    $this->cotisation_prix_details->getOrAdd('BOUTEILLES_NORMALES')->addDetail($partBouteilleNormale, $quantiteBouteilleNormale, $prixUnitaireBouteilleNormale, $prixBouteilleNormale);
    $this->cotisation_prix_details->getOrAdd('BOUTEILLES_ALLEGEES')->addDetail($partBouteilleAllegee, $quantiteBouteilleAllegee, $prixUnitaireBouteilleAllegee, $prixBouteilleAllegee);
    $this->cotisation_prix_details->getOrAdd('BOUTEILLES_CARTONS')->addDetail(round($partCarton,3), $quantiteCarton, $prixUnitaireCarton, $prixBouteilleCarton);
    $this->cotisation_prix_details->getOrAdd('BIB_3L')->addDetail($partBib3L, $quantiteBib3L, $prixUnitaireBib3L, $prixBib3L);
    $this->cotisation_prix_details->getOrAdd('BIB_5L')->addDetail($partBib5L, $quantiteBib5L, $prixUnitaireBib5L, $prixBib5L);
    $this->cotisation_prix_details->getOrAdd('BIB_10L')->addDetail($partBib10L, $quantiteBib10L, $prixUnitaireBib10L, $prixBib10L);
    $this->cotisation_prix_total = $prixTotal;
  }

}
