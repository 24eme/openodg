<?php

class DeclarationTousView extends acCouchdbView
{
    const KEY_TYPE = 0;
    const KEY_CAMPAGNE = 1;
    const KEY_IDENTIFIANT = 2;
    const KEY_MODE = 3;
    const KEY_STATUT = 4;
    const KEY_PRODUIT = 5;
    const KEY_DATE = 6;
    const KEY_INFOS = 7;
    const KEY_RAISON_SOCIALE = 8;
    const KEY_COMMUNE = 9;
    const KEY_EMAIL = 10;
    const KEY_CVI = 11;

    const MODE_TELDECLARATION = "Télédeclaration";
    const MODE_SAISIE_INTERNE = "Saisie interne";
    const MODE_IMPORTE = "Importé";

    const STATUT_BROUILLON = "Brouillon";
    const STATUT_A_VALIDER = "À valider";
    const STATUT_VALIDE = "Validé";
    const STATUT_EN_ATTENTE = "En attente";

    public static function getInstance() {

        return acCouchdbManager::getView('declaration', 'tous', 'Declaration');
    }

    public static function constructIdentifiantDocument($result,$facetName =""){
      return $result->id.$facetName;
    }

}
