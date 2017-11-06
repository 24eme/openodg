<?php

class HabilitationActiviteView extends acCouchdbView
{
    const KEY_STATUT = 0;
    const KEY_ACTIVITE = 1;
    const KEY_PRODUIT_LIBELLE = 2;
    const KEY_DATE = 3;
    const KEY_IDENTIFIANT = 4;
    const KEY_RAISON_SOCIALE = 5;
    const KEY_CVI = 6;
    const KEY_SIRET = 7;
    const KEY_PRODUIT_HASH = 8;
    const KEY_COMMENTAIRE = 9;
    const KEY_ADRESSE = 10;
    const KEY_CODE_POSTAL = 11;
    const KEY_COMMUNE = 12;
    const KEY_EMAIL = 13;

    public static function getInstance() {

        return acCouchdbManager::getView('habilitation', 'activite', 'Habilitation');
    }

}
