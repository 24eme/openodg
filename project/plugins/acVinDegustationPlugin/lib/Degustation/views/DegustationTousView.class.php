<?php 

class DegustationTousView extends acCouchdbView
{
    const KEY_APPELLATION = 0;
    const KEY_IDENTIFIANT = 1;
    const KEY_DATE_DEGUSTATION = 2;
    const KEY_STATUT = 3;
    const KEY_DREV = 4;

    public static function getInstance() {

        return acCouchdbManager::getView('degustation', 'tous', 'Degustation');
    }

    public function getDegustationsByAppellation($appellation) {

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
    }

}