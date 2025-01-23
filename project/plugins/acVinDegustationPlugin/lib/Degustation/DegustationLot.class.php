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
        if($this->numero_table === null) {

            return false;
        }

        return true;
    }

    public function anonymize($index)
    {
        $table_anno = '';
        if (DegustationConfiguration::getInstance()->hasAlwaysIdentifiantTable() || ($this->getDocument()->getLastNumeroTable() >= 2)) {
            $table_anno = $this->getNumeroTableStr();
        }
        $this->numero_anonymat = sprintf(DegustationConfiguration::getInstance()->getFormatAnonymat(), $table_anno, $index + 1);
    }

    public function setNumeroAnonymat($numero) {
        $this->_set('numero_anonymat', $numero);
        if($numero && !$this->conformite) {
            $this->statut = Lot::STATUT_ANONYMISE;
        }
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

    public function leverNonConformite($date = null)
    {
        if(!$date){
            $date = date('Y-m-d');
        }
        $this->statut = Lot::STATUT_NONCONFORME_LEVEE;
        $this->nonconformite_levee = $date;
        $this->getDocument()->generateMouvementsLots();
    }

    public function setConformiteLot($conformite, $motif = null, $observation = null)
    {
        $this->conformite = $conformite;
        $this->setMotif($motif);
        $this->setObservation($observation);

        if ($conformite === Lot::CONFORMITE_CONFORME) {
            $this->statut = Lot::STATUT_CONFORME;
        } else {
            $this->statut = Lot::STATUT_NONCONFORME;
        }
    }

    public function setConformite($conformite){
        if(!$this->_get("conformite")){

            $this->getDocument()->generateMouvementsFacturesOnNextSave = true;

            return $this->_set("conformite",$conformite);
        }
        if(!in_array(Lot::CONFORMITE_CONFORME,array($this->_get("conformite"), $conformite))){
            return $this->_set("conformite",$conformite);
        }

        if($this->_get("conformite") != $conformite){

            $this->getDocument()->generateMouvementsFacturesOnNextSave = true;
        }
        return $this->_set("conformite",$conformite);

    }

    public function setIsPreleve($date = null) {
        if($this->isAnnule()) {
            $this->preleve = null;
            $this->statut = Lot::STATUT_ANNULE;
            return;
        }

        if (!$date) {
            $date = date('Y-m-d');
        }
        $this->preleve = $date;
        $this->statut = Lot::STATUT_PRELEVE;
    }

    public function setVolume($volume) {
        $this->_set('volume', $volume);
        if($this->isAnnule()) {
            $this->preleve = null;
            $this->statut = Lot::STATUT_ANNULE;
        }
    }

    public function isPrelevable() {
        if($this->isLeurre()) {
            return false;
        }

        if($this->isAnnule()) {
            return false;
        }

        return true;
    }

    public function isLotTournee() {
        return strpos($this->id_document_provenance, TourneeClient::TYPE_COUCHDB) !== false;
    }

    public function isDegustable() {
        if( ! $this->isLotTournee() && ! $this->isPreleve() && ! $this->isLeurre()) {
            return false;
        }
        if($this->isAnnule()) {
            return false;
        }
        if($this->isDiffere()) {
            return false;
        }

        return true;
    }

    public function isPreleve() {
        return $this->preleve !== null;
    }

    public function isAnnule() {

        return $this->volume === 0;
    }

    public function getDocumentType()
    {
        return $this->getDocument()->getType();
    }

    public function getDocumentOrdre() {
        if ($this->getDocument()->getType() == TourneeClient::TYPE_MODEL && in_array($this->initial_type, array_keys(TourneeClient::$lotTourneeChoices)) && !$this->id_document_provenance) {
            $this->_set('document_ordre', "01");
        } else {
            $this->_set('document_ordre', $this->getDocumentOrdreCalcule());
        }

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

    public function setEmailEnvoye($s) {
        if ($s && !strtotime($s)) {
            throw new sfException('On ne peut pas générer la date de notification avec un email envoyé ayant pour valeur '.$s);
        }
        return $this->_set('email_envoye', $s);
    }

}
