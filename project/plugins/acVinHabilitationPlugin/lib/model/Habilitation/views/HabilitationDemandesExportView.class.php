<?php

class HabilitationDemandesExportView extends acCouchdbView
{
    const KEY_DATE = 0;
    const KEY_STATUT = 1;
    const KEY_PRODUIT = 2;
    const KEY_LIBELLE = 3;
    const KEY_DATE_HABILITATION = 4;
    const KEY_DEMANDE_KEY = 5;
    const KEY_DEMANDE = 6;
    const KEY_IDENTIFIANT = 7;

    public static function getInstance() {

        return acCouchdbManager::getView('habilitation', 'demandesExport', 'Habilitation');
    }

    public function getExportForDateAndStatut($date,$statut){
        return $this->client->startkey(array($date,$statut))
    				->endkey(array($date,$statut,array()))
    				->getView($this->design, $this->view)->rows;
    }

}
