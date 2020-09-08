<?php
/**
 * Model for DegustationLot
 *
 */

class DegustationLot extends BaseDegustationLot {

  public function isNonConforme(){
    return $this->statut == Lot::STATUT_NON_CONFORME;
  }

}
