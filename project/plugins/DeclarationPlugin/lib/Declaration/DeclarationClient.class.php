<?php

class DeclarationClient
{
    protected static $self = null;

    public static function getInstance() {
        if(is_null(self::$self)) {

            self::$self = new DeclarationClient();
        }

        return self::$self;
    }

    public function find($id) {

        return acCouchdbManager::getClient()->find($id);
    }

    public function getExportCsvClassName($type) {
        if($type == DRevClient::TYPE_MODEL) {

            return 'ExportDRevCSV';
        }

        if($type == ParcellaireClient::TYPE_MODEL) {

            return 'ExportParcellaireCSV';
        }

        if($type == DRevMarcClient::TYPE_MODEL) {

            return 'ExportDRevMarcCSV';
        }

        throw new sfException(sprintf("Le type de document %s n'a pas de classe d'export correspondante", $type));
    }

    public function getExportCsvObject($doc, $header = true) {
        $className = $this->getExportCsvClassName($doc->type);

        return new $className($doc, $header);
    }

    public function getTypesAndCampagne() {
        $typeAndCampagne = array();

        $rows = acCouchdbManager::getClient()
                    ->reduce(true)
                    ->group_level(2)
                    ->getView('declaration', 'tous')->rows;

        foreach($rows as $row) {
            $item = new stdClass();
            $item->type = $row->key[DeclarationTousView::KEY_TYPE];
            $item->campagne = $row->key[DeclarationTousView::KEY_CAMPAGNE];
            $typeAndCampagne[$item->campagne."_".$item->type] = $item;
        }

        return $typeAndCampagne;
    }

    public function getIds($type, $campagne, $validation = true) {
        $ids = array();

        $rows = acCouchdbManager::getClient()
                    ->startkey(array($type, $campagne))
                    ->endkey(array($type, $campagne, array()))
                    ->reduce(false)
                    ->getView('declaration', 'tous')->rows;

        foreach($rows as $row) {
            $ids[] = $row->id;
        }

        return $ids;
    }
}