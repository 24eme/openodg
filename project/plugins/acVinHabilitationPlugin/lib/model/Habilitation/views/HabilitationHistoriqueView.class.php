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

    public static function getInstance() {

        return acCouchdbManager::getView('habilitation', 'historique', 'Habilitation');
    }

    public function getByDateAndStatut($date,$statut){
        return $this->client->startkey(array($date,$statut))
    				->endkey(array($date,$statut,array()))
    				->getView($this->design, $this->view)->rows;
    }

}
