<?php

class DeclarationExportView extends acCouchdbView
{
    const KEY_TYPE = 0;
    const KEY_CAMPAGNE = 1;
    const KEY_IDENTIFIANT = 2;

    public static function getInstance() {

        return acCouchdbManager::getView('declaration', 'export', 'Declaration');
    }

}
