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

    public function getHistoriques() {
        $historiques = array();
        foreach($this->getDocument()->historique as $h) {
            if(!preg_match("/".$this->getKey()."/", $h->iddoc)) {
                continue;
            }
            $historiques[] = $h;
        }

        return $historiques;
    }

    public function getHistoriqueFirstNext() {
        $historiques = $this->getHistoriques();

        if(!count($historiques)) {
            $next = $this->getDocument()->getNext();

            return ($next && $next->exist($this->getHash())) ? $next->get($this->getHash())->getHistoriqueFirstNext() : null;
        }

        return array_shift($historiques);
    }

    public function getHistoriqueLastPrevious() {
        $historiques = $this->getHistoriques();

        if(!count($historiques)) {
            $previous = $this->getDocument()->getPrevious();
            return ($previous && $previous->exist($this->getHash())) ? $previous->get($this->getHash())->getHistoriqueLastPrevious() : null;
        }

        return array_pop($historiques);
    }

    public function getHistoriquePrecedent($statut, $date) {
        $historiques = $this->getHistoriques();

        $prev = null;
        foreach($historiques as $h) {
            if($prev && $h->statut == $statut && $h->date == $date) {

                return $prev;
            }
            $prev = $h;
        }

        $habilitationPrecedente = $this->getDocument()->getPrevious();

        if($habilitationPrecedente && $habilitationPrecedente->exist($this->getHash())) {

            return $habilitationPrecedente->get($this->getHash())->getHistoriqueLastPrevious();
        }

        return null;
    }

    public function getHistoriqueSuivant($statut, $date) {
        $historiques = $this->getHistoriques();

        $finded = false;
        foreach($historiques as $h) {
            if($finded == true) {
                return $h;
            }
            if($h->statut == $statut && $h->date == $date) {
                $finded = true;
            }
        }

        $habilitationSuivante = $this->getDocument()->getNext();

        if($habilitationSuivante && $habilitationSuivante->exist($this->getHash())) {

            return $habilitationSuivante->get($this->getHash())->getHistoriqueFirstNext();
        }

        return null;
    }

}
