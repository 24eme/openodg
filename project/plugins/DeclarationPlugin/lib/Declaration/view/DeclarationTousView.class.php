<?php

class DeclarationTousView extends acCouchdbView
{
    const KEY_TYPE = 0;
    const KEY_CAMPAGNE = 1;
    const KEY_MODE = 2;
    const KEY_STATUT = 3;
    const KEY_IDENTIFIANT = 4;
    const KEY_DATE = 5;
    const KEY_INFOS = 6;
    const KEY_RAISON_SOCIALE = 7;
    const KEY_COMMUNE = 8;
    const KEY_EMAIL = 9;

    const MODE_TELDECLARATION = "Télédeclaration";
    const MODE_SAISIE_INTERNE = "Saisie interne";
    const MODE_IMPORTE = "Importé";

    const STATUT_BROUILLON = "Brouillon";
    const STATUT_A_VALIDER = "À valider";
    const STATUT_VALIDE = "Validé";

    public static function getInstance() {

        return acCouchdbManager::getView('declaration', 'tous', 'Declaration');
    }

}
