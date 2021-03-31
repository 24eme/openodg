<?php
/**
 * Model for DegustationLot
 *
 */

class DegustationLot extends BaseDegustationLot {

  public function getEtablissement() {
      return EtablissementClient::getInstance()->findByIdentifiant($this->declarant_identifiant);
  }

  public function isNonConforme(){
    return ($this->statut == Lot::STATUT_NONCONFORME);
  }

  public function isRecoursOC()
  {
    return $this->statut === Lot::STATUT_RECOURS_OC;
  }

  public function isManquement()
  {
    return $this->isRecoursOC() || $this->isNonConforme();
  }

  public function isConformeObs(){
    return ($this->statut == Lot::STATUT_CONFORME) && $this->exist('observation') && $this->observation;
  }

  public function getShortLibelleConformite(){
    if($this->isConformeObs()){ return 'Obs.'; }
        $libelles = Lot::$shortLibellesConformites;
        return ($this->exist('conformite') && isset($libelles[$this->conformite]))? $libelles[$this->conformite] : $this->conformite;
  }

  public function getNumeroTableStr() {
      if (!$this->numero_table) {
          return '';
      }
      return DegustationClient::getNumeroTableStr($this->numero_table);
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

    public function isAnonymisable(){
        return !is_null($this->numero_table);
    }

    public function anonymize($index)
    {
        $this->numero_anonymat = $this->getNumeroTableStr().($index+1);
        $this->statut = Lot::STATUT_ANONYMISE;
    }

    public function recoursOc($date = null){
        if(!$date){
            $date = date('Y-m-d');
        }
        $this->recours_oc = $date;
        $this->statut = Lot::STATUT_RECOURS_OC;
    }

    public function conformeAppel($date = null)
    {
        if(!$date){
            $date = date('Y-m-d');
        }
        $this->statut = Lot::STATUT_CONFORME_APPEL;
        $this->conforme_appel = $date;
        $this->getDocument()->generateMouvementsLots();
    }

    public function setConformiteLot($conformite, $motif = null, $observation = null)
    {
        if ($this->conformiteEditable() === false) {
            throw new sfException('Impossible de changer la conformité du lot '.$this->getUniqueId());
        }

        $this->conformite = $conformite;
        $this->setMotif($motif);
        $this->setObservation($observation);

        if ($conformite === Lot::CONFORMITE_CONFORME) {
            $this->statut = Lot::STATUT_CONFORME;
        } else {
            $this->statut = Lot::STATUT_NONCONFORME;
        }
    }

    public function conformiteEditable()
    {
        if ($this->getMouvement(Lot::STATUT_RECOURS_OC)) {
            return false;
        }

        return true;
    }

    public function setConformite($conformite){
        if(!$this->_get("conformite")){

            // MouvementsFacture => On ne génére les mouvements de facture qu'a l'anonymat et la mise en non conformité
            $this->getDocument()->clearMouvementsFactures();
            $this->getDocument()->generateMouvementsFactures();

            return $this->_set("conformite",$conformite);
        }
        if(!in_array(Lot::CONFORMITE_CONFORME,array($this->_get("conformite"), $conformite))){
            return $this->_set("conformite",$conformite);
        }

        if($this->_get("conformite") != $conformite){

            // MouvementsFacture => On ne génére les mouvements de facture qu'a l'anonymat et la mise en non conformité
            $this->getDocument()->clearMouvementsFactures();
            $this->getDocument()->generateMouvementsFactures();
        }
        return $this->_set("conformite",$conformite);

    }

    public function getDocumentType() {

        return DegustationClient::TYPE_MODEL;
    }

    public function getDocumentOrdre() {
        return $this->_get('document_ordre');
    }

    public function getLibelle() {

        return parent::getLibelle();
    }

    public function getMouvementFreeInstance() {

        return DegustationMouvementLots::freeInstance($this->getDocument());
    }

    public function setNumeroTable($n) {
        if ($n) {
            $this->statut = Lot::STATUT_ATTABLE;
        }
        return parent::setNumeroTable($n);
    }

}
