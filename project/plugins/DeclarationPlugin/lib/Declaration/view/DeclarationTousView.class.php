<?php 

class DeclarationTousView extends acCouchdbView
{
    const KEY_TYPE = 0;
    const KEY_CAMPAGNE = 1;

    public static function getInstance() {

        return acCouchdbManager::getView('declaration', 'tous', 'Declaration');
    }

    /*public function getDegustationsByAppellation($appellation) {

        return $this->viewToJson($this->client
                            ->startkey(array($appellation))
                            ->endkey(array($appellation, array()))
                            ->reduce(false)
                            ->getView($this->design, $this->view)->rows);;
    }

    public function viewToJson($rows) {
        $items = array();

        foreach($rows as $row) {

            $key = $row->key[self::KEY_IDENTIFIANT];

            $item = new stdClass();

            $item->_id = $row->id;
            $item->date_degustation = $row->key[self::KEY_DATE_DEGUSTATION];
            $item->appellation = $row->key[self::KEY_APPELLATION];
            $item->identifiant = $row->key[self::KEY_IDENTIFIANT];
            $item->statut = $row->key[self::KEY_STATUT];
            $item->drev = $row->key[self::KEY_DREV];
            $item->nb_prelevements = $row->value;

            $items[$key] = $item;
        }

        return $items;
    }*/

}