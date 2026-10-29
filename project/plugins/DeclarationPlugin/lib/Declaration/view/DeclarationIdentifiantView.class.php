<?php

class DeclarationIdentifiantView extends acCouchdbView
{
    const KEY_IDENTIFIANT = 1;
    const KEY_CAMPAGNE = 1;
    const KEY_TYPE = 2;

    public static function getInstance() {
        return acCouchdbManager::getView('declaration', 'identifiant');
    }

    public function getByIdentifiant($identifiant) {

        return $this->client->startkey([$identifiant, null])
                            ->endkey(array($identifiant, array()))
                            ->group(2)
                            ->reduce(true)
                            ->getView($this->design, $this->view);
    }
}
