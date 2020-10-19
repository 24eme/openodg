<?php
/**
 * Model for DegustationLot
 *
 */

class DegustationLot extends BaseDegustationLot {

  public function isNonConforme(){
    return ($this->statut == Lot::STATUT_DEGUSTE) && $this->exist('conformite') && in_array($this->conformite,Lot::$nonConformites);
  }

  public function isConformeObs(){
    return ($this->statut == Lot::STATUT_DEGUSTE) && $this->exist('conformite') && ($this->conformite == Lot::CONFORMITE_CONFORME) && $this->exist('observation') && $this->observation;
  }

  public function getShortLibelleConformite(){
    if($this->isConformeObs()){ return 'Obs.'; }
        $libelles = Lot::$shortLibellesConformites;
        return ($this->exist('conformite') && isset($libelles[$this->conformite]))? $libelles[$this->conformite] : $conformite;
  }

}
