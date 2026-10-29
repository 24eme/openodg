<?php

class DeclarationExportView extends acCouchdbView
{
    const KEY_TYPE = 0;
    const KEY_CAMPAGNE = 1;
    const KEY_IDENTIFIANT = 2;

    public static function getInstance() {

        return acCouchdbManager::getView('declaration', 'export');
    }

    public function getDeclarations($type, $campagne = null) {
      $keys = ($campagne)? array($type, $campagne) : array($type);
      return $this->client
            ->startkey($keys)
            ->endkey(array_merge($keys, array(array())))
            ->reduce(false)
            ->getView($this->design, $this->view);
    }

}
