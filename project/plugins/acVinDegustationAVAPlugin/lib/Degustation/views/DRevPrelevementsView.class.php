<?php

class DRevPrelevementsView extends acCouchdbView
{
    const KEY_FORCE = 0;
    const KEY_APPELLATION = 1;
    const KEY_DATE = 2;
    const KEY_IDENTIFIANT = 3;
    const KEY_RAISON_SOCIALE = 4;
    const KEY_ADRESSE = 5;
    const KEY_CODE_POSTAL = 6;
    const KEY_COMMUNE = 7;

    public static function getInstance() {

        return acCouchdbManager::getView('drev', 'prelevements', 'DRev');
    }

    public function getPrelevements($force, $produit, $date_from = null, $date_to = null, $campagne = null) {
        $startkey = [$force, 'cuve_'.$produit];
        $endkey = [$force, 'cuve_'.$produit];

        if ($date_from) { $startkey[] = $date_from; }

        if ($date_to) {
            $endkey[] = $date_to;
        }
        $endkey[] = [];

        return $this->viewToJson($this->client
                            ->startkey($startkey)
                            ->endkey($endkey)
                            ->reduce(false)
                            ->getView($this->design, $this->view)->rows, $campagne);
    }

    public function viewToJson($rows, $campagne) {
        $items = array();

        foreach($rows as $row) {
            if($campagne && preg_replace("/DREV-[0-9]+-/", "", $row->id) != $campagne) {

                continue;
            }

            $key = $row->key[self::KEY_APPELLATION].$row->key[self::KEY_DATE].$row->key[self::KEY_IDENTIFIANT];

            if(array_key_exists($key, $items)) {
                $item = $items[$key];
            } else {
                $item = new stdClass();
                $item->lots = array();
            }

            $item->_id = $row->id;
            $item->date = $row->key[self::KEY_DATE];
            $item->appellation = $row->key[self::KEY_APPELLATION];
            $item->identifiant = $row->key[self::KEY_IDENTIFIANT];
            $item->raison_sociale = $row->key[self::KEY_RAISON_SOCIALE];
            $item->adresse = $row->key[self::KEY_ADRESSE];
            $item->code_postal = $row->key[self::KEY_CODE_POSTAL];
            $item->commune = $row->key[self::KEY_COMMUNE];
            $item->force = $row->key[self::KEY_FORCE];

            $items[$key] = $item;
        }

        return $items;
    }

}
