<?php
/**
 * Model for DegustationLot
 *
 */

class DegustationLot extends BaseDegustationLot {

  public function isNonConforme(){
    return ($this->statut == Lot::STATUT_NONCONFORME);
  }

  public function isConformeObs(){
    return ($this->statut == Lot::STATUT_CONFORME) && $this->exist('observation') && $this->observation;
  }

  public function getShortLibelleConformite(){
    if($this->isConformeObs()){ return 'Obs.'; }
        $libelles = Lot::$shortLibellesConformites;
        return ($this->exist('conformite') && isset($libelles[$this->conformite]))? $libelles[$this->conformite] : $conformite;
  }

  public function getNumeroTableStr() {
      if (!$this->numero_table) {
          return '';
      }
      return DegustationClient::getNumeroTableStr($this->numero_table);
  }

  public function getNumeroAnonymise() {
    if (!$this->numero_table) {
        return '';
    }
    $table = $this->getNumeroTableStr();
    foreach($this->getDocument()->getLotsByTable($this->numero_table) as $k => $v) {
      if ($v->getUnicityKey() == $this->getUnicityKey()) {
        return $table.($k+1);
      }
    }
    return '';
  }

}
