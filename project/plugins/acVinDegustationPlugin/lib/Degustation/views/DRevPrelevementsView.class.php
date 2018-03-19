<?php

class DRevPrelevementsView extends acCouchdbView
{
    const KEY_APPELLATION = 0;
    const KEY_DATE = 1;
    const KEY_IDENTIFIANT = 2;
    const KEY_RAISON_SOCIALE = 3;
    const KEY_ADRESSE = 4;
    const KEY_CODE_POSTAL = 5;
    const KEY_COMMUNE = 6;
    const KEY_FORCE = 7;

    public static function getInstance() {

        return acCouchdbManager::getView('drev', 'prelevements', 'DRev');
    }

    public function getPrelevements($produit, $date_from, $date_to, $campagne = null) {

        return $this->viewToJson($this->client
                            ->startkey(array("cuve_".$produit, $date_from))
                            ->endkey(array("cuve_".$produit, $date_to, array()))
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
