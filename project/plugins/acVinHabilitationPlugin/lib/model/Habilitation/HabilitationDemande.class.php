<?php
/**
 * Model for HabilitationHistorique
 *
 */

class HabilitationDemande extends BaseHabilitationDemande {

    public function getConfig()
    {
        return $this->getDocument()->getConfiguration()->get($this->getProduit());
    }


    public function setProduit($hash) {
        $this->_set('produit', $hash);
        $this->produit_libelle = $this->getConfig()->getLibelleComplet();
    }

    public function getLibelle() {
        if($this->_get('libelle')) {

            return $this->_get('libelle');
        }

        $this->libelle = $this->getProduitLibelle().": ".implode(", ", $this->getActivitesLibelle())    ;

        return $this->_get('libelle');
    }

    public function getDemandeLibelle() {

        return HabilitationClient::$demande_libelles[$this->demande];
    }

    public function getStatutLibelle() {

        return HabilitationClient::getInstance()->getDemandeStatutLibelle($this->statut);
    }

    public function isOuvert() {

        return !(in_array($this->statut, array('VALIDE', 'REFUSE', 'ANNULE')));
    }

    public function getActivitesLibelle() {
        $activitesLibelle = array();

        foreach($this->activites as $activite) {
            $activitesLibelle[] = HabilitationClient::getInstance()->getLibelleActivite($activite);
        }

        return $activitesLibelle;
    }

    public function getlastCompletudeDemande(){
            return HabilitationClient::getInstance()->findLastDemandeByStatut($this->getDocument()->identifiant,$this->getDocument()->date,"COMPLET",$this->getKey());

    }

    public function getNextStatut(){
            $nextHabilitation = HabilitationClient::getInstance()->findNextByIdentifiantAndDate($this->getDocument()->identifiant,$this->getDocument()->date);
            if(!$nextHabilitation) return null;
            if(!$nextHabilitation->demandes->exist($this->getKey())) return null;
            $nextDemande = $nextHabilitation->demandes->get($this->getKey());
            return $nextDemande->statut;

    }
}
