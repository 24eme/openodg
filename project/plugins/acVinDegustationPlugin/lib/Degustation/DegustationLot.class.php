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

  public function isSecondPassage(){
    return strpos($this->specificite, "2ème") !== false ? true : false;
  }

  public function getTextPassage($enLettre = true){
    if($enLettre)
      return $this->isSecondPassage() ? "second" : "premier";
    return $this->isSecondPassage() ? "2nd" : "1er";
  }

  public function isConditionnement(){
    return preg_match('/'.ConditionnementClient::TYPE_COUCHDB.'/', $this->id_document);
  }

  public function getTypeLot(){
    if(preg_match('/'.ConditionnementClient::TYPE_COUCHDB.'/', $this->id_document)){
      return 'Cond';
    }

    if(preg_match('/'.DRevClient::TYPE_COUCHDB.'/', $this->id_document)){
      return 'DRev';
    }
  }

  public function redegustation($degustation_originale)
    {
        // Duplique le mouvement
        // nouveau mouvement -> prélevable
        // nouveau mouvement -> specificité Xeme passage
        // on espere que le nouveau mvmt est listé dans les 'a prelever'
        // on créé le nouveau lot à partir de ce mouvement
        // on le rentre dans la dégust

        $mvmt_lot = $degustation_originale->mouvements_lots
                                          ->get($this->declarant_identifiant)
                                          ->get($this->getUnicityKey());

        $newmvmt = clone $mvmt_lot;
        $newmvmt->statut = Lot::STATUT_PRELEVABLE;
        $newmvmt->updatePassage();

        return $newmvmt;
    }

}
