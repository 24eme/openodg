<?php
/**
 * Model for Rendezvous
 *
 */

class Rendezvous extends BaseRendezvous {

    
    public function constructId() {
        $this->set('_id', sprintf("%s-%s-%s", RendezvousClient::TYPE_COUCHDB, $this->cvi, str_replace("-", "", $this->date).str_replace(":", "", $this->heure)));
    }
    
    public function getDateHeure(){
        return str_replace('-','',$this->getDate()).str_replace(':','',$this->getHeure());
    }
    
    public function getCompte() {
        return CompteClient::getInstance()->findByIdentifiant($this->identifiant);
    }
    
    public function getChai() {
        return $this->getCompte()->getChais()->get($this->idchai);
    }
    
    public function isRendezvousRaisin() {
        return RendezvousClient::RENDEZVOUS_TYPE_RAISIN == $this->type_rendezvous;
    }
    
    public function isRendezvousVolume() {
        return RendezvousClient::RENDEZVOUS_TYPE_VOLUME == $this->type_rendezvous;
    }
    
    public function isRealise() {
        return RendezvousClient::RENDEZVOUS_STATUT_REALISE == $this->statut;
    }
    
    public function getDateHeureFr(){
        return ucfirst(format_date($this->getDate(), "P", "fr_FR"))."&nbsp;".str_replace(':','h',$this->getHeure());
    }
}