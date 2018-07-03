<?php

class HabilitationDemandeView extends acCouchdbView
{
    const KEY_DEMANDE = 0;
    const KEY_STATUT = 1;
    const KEY_LIBELLE = 2;
    const KEY_DATE = 3;
    const KEY_DEMANDE_KEY = 4;
    const KEY_IDENTIFIANT = 5;
    const KEY_COMMENTAIRE = 6;

    public static function getInstance() {

        return acCouchdbManager::getView('habilitation', 'activite', 'Habilitation');
    }

}
