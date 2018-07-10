<?php

class HabilitationDemandeView extends acCouchdbView
{
    const KEY_DEMANDE = 0;
    const KEY_STATUT = 1;
    const KEY_PRODUIT = 2;
    const KEY_LIBELLE = 3;
    const KEY_DATE = 4;
    const KEY_DEMANDE_KEY = 5;
    const KEY_IDENTIFIANT = 6;
    const KEY_COMMENTAIRE = 7;

    public static function getInstance() {

        return acCouchdbManager::getView('habilitation', 'activite', 'Habilitation');
    }

}
