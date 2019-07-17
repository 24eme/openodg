<?php

class HabilitationHistoriqueView extends acCouchdbView
{
    const KEY_DATE = 0;
    const KEY_STATUT = 1;
    const KEY_IDENTIFIANT = 2;
    const KEY_DESCRIPTION = 3;
    const KEY_COMMENTAIRE = 4;
    const KEY_AUTEUR = 5;
    const KEY_IDDOC = 6;
    
    const KEY_TYPEDEMANDE = 7; // demande
    const KEY_DATEHABILITATION = 8; // date_habilitation
    const KEY_PRODUIT = 9; // produit
    const KEY_PRODUITLIBELLE = 9; // produit_libelle
    const KEY_ACTIVITES = 10; // activites
    
    
    

    public static function getInstance() {

        return acCouchdbManager::getView('habilitation', 'historique', 'Habilitation');
    }

    public function getAll() {
        return $this->client->getView($this->design, $this->view)->rows;
    }

    public function getByDate($dateFrom, $dateTo){
        return $this->client->startkey(array($dateFrom))
                    ->endkey(array($dateTo,array()))
                    ->getView($this->design, $this->view)->rows;
    }

    public function getByDateAndStatut($date,$statut){
        return $this->client->startkey(array($date,$statut))
    				->endkey(array($date,$statut,array()))
    				->getView($this->design, $this->view)->rows;
    }

}
