<?php
/**
 * Model for HabilitationHistorique
 *
 */

class HabilitationDemandeDonnees extends BaseHabilitationDemandeDonnees {

    public function getActivitesLibelle() {
        if (!$this->exist('activites')) {
            return null;
        }
        $activitesLibelles = array();
        foreach($this->activites as $activite) {
            $activitesLibelles[] = HabilitationClient::$activites_libelles[$activite];
        }

        return "Activités: ". implode(", ", $activitesLibelles);
    }

    public function getProduitLibelle() {

        return $this->getDocument()->getConfiguration()->get($this->getProduit())->getLibelleComplet();
    }
}
