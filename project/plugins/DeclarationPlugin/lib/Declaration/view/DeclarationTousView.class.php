<?php 

class DeclarationTousView extends acCouchdbView
{
    const KEY_TYPE = 0;
    const KEY_CAMPAGNE = 1;
    const KEY_VALIDATION = 2;
    const KEY_VALIDATION_ODG = 3;
    const KEY_ETAPE = 4;
    const KEY_IDENTIFIANT = 5;
    const KEY_NB_DOC_EN_ATTENTE = 6;
    const KEY_PAPIER = 7;
    const KEY_AUTOMATIQUE = 8;
    const KEY_RAISON_SOCIALE = 9;
    const KEY_COMMUNE = 10;
    const KEY_EMAIL = 11;

    public static function getInstance() {

        return acCouchdbManager::getView('declaration', 'tous', 'Declaration');
    }

}