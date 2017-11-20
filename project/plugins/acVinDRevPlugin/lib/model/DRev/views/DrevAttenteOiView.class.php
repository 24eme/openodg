<?php

class DrevAttenteOiView extends acCouchdbView
{
    const KEY_CAMPAGNE = 0;
    const KEY_IDENTIFIANT = 1;

    public static function getInstance() {
        return acCouchdbManager::getView('drev', 'attente_oi', 'DRev');
    }
    
 	public function getAll() {
        return $this->client->getView($this->design, $this->view)->rows;
 	}

}
