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

        return !(in_array($this->statut, HabilitationClient::getInstance()->getStatutsFerme()));
    }

    public function getActivitesLibelle() {
        $activitesLibelle = array();

        foreach($this->activites as $activite) {
            $activitesLibelle[$activite] = HabilitationClient::getInstance()->getLibelleActivite($activite);
        }

        return $activitesLibelle;
    }

    public function getFullHistorique() {
        $historiques = array();
        foreach($this->getDocument()->getFullHistorique() as $h) {
            if(!preg_match("/".$this->getKey()."/", $h->iddoc)) {
                continue;
            }
            $historiques[] = $h;
        }

        return $historiques;
    }

    public function getExtractHistoriqueFromStatut($statut) {
        foreach($this->getFullHistorique() as $h) {
            if ($h->statut == $statut) {
                return $h;
            }
        }
        return $h;
    }

    public function getHistoriquePrecedent($statut, $date) {
        $prev = null;
        $habilitationLast = HabilitationClient::getInstance()->getLastHabilitation($this->getDocument()->identifiant);

        if(!$habilitationLast->exist($this->getHash())) {

            return null;
        }
        foreach($habilitationLast->get($this->getHash())->getFullHistorique() as $h) {
            if($prev && $h->statut == $statut && $h->date == $date) {

                return $prev;
            }
            $prev = $h;
        }

        return null;
    }

    public function getHistoriqueSuivant($statut, $date) {
        $finded = false;
        $habilitationLast = HabilitationClient::getInstance()->getLastHabilitation($this->getDocument()->identifiant);

        if(!$habilitationLast->exist($this->getHash())) {

            return null;
        }

        foreach($habilitationLast->get($this->getHash())->getFullHistorique() as $h) {
            if($finded == true) {
                return $h;
            }
            if($h->statut == $statut && $h->date == $date) {
                $finded = true;
            }
        }

        return null;
    }

}
