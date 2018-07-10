<?php

class HabilitationDemandeView extends acCouchdbView
{
    const KEY_DEMANDE = 0;
    const KEY_STATUT = 1;
    const KEY_PRODUIT = 2;
    const KEY_LIBELLE = 3;
    const KEY_DATE = 4;
    const KEY_DATE_HABILITATION = 5;
    const KEY_DEMANDE_KEY = 6;
    const KEY_IDENTIFIANT = 7;
    const KEY_COMMENTAIRE = 8;

    public static function getInstance() {

        return acCouchdbManager::getView('habilitation', 'activite', 'Habilitation');
    }

}
