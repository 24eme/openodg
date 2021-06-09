<?php

class DeclarationClient
{
    protected static $self = null;
    const REGION_LOT = 'IGP_VALDELOIRE';

    public $findCache = array();

    public static function getInstance() {
        if(is_null(self::$self)) {

            self::$self = new DeclarationClient();
        }

        return self::$self;
    }

    public function find($id, $hydrate = acCouchdbClient::HYDRATE_DOCUMENT, $force_return_ls = false) {

        return acCouchdbManager::getClient()->find($id, $hydrate, $force_return_ls);
    }

    public function clearCache() {
        $this->findCache = array();
    }

    public function findCache($id) {
        if(!array_key_exists($id, $this->findCache)) {
            $this->findCache[$id] = $this->find($id);
        }

        return $this->findCache[$id];
    }

    public function getExportCsvClassName($type) {
        if(class_exists("DRevClient") && $type == DRevClient::TYPE_MODEL) {

            return 'ExportDRevCSV';
        }

        if(class_exists("ParcellaireClient") && $type == ParcellaireClient::TYPE_MODEL) {

            return 'ExportParcellaireCSV';
        }

        if(class_exists("TirageClient") && $type == TirageClient::TYPE_MODEL) {

            return 'ExportTirageCSV';
        }

        if(class_exists("DRevMarcClient") && $type == DRevMarcClient::TYPE_MODEL) {

            return 'ExportDRevMarcCSV';
        }

        if(class_exists("ConstatsClient") && $type == ConstatsClient::TYPE_MODEL) {

            return 'ExportConstatsCSV';
        }

        if(class_exists("FactureClient") && $type == FactureClient::TYPE_MODEL) {

            return 'ExportFactureCSV_'.ucfirst(sfConfig::get('sf_app'));
        }

        if(class_exists("TravauxMarcClient") && $type == TravauxMarcClient::TYPE_MODEL) {

            return 'ExportTravauxMarcCSV';
        }

        if(class_exists("DRClient") && $type == DRClient::TYPE_MODEL) {

            return 'ExportDRCSV';
        }

        if(class_exists("SV11Client") && $type == SV11Client::TYPE_MODEL) {

            return 'ExportSV11CSV';
        }

        if(class_exists("SV12Client") && $type == SV12Client::TYPE_MODEL) {

            return 'ExportSV12CSV';
        }

        if(class_exists("HabilitationClient") && $type == HabilitationClient::TYPE_MODEL) {

            return 'ExportHabilitationCSV';
        }

        if(class_exists("ParcellaireIrrigableClient") && $type == ParcellaireIrrigableClient::TYPE_MODEL) {

            return 'ExportParcellaireIrrigableCSV';
        }

        if(class_exists("ParcellaireIrrigueClient") && $type == ParcellaireIrrigueClient::TYPE_MODEL) {

            return 'ExportParcellaireIrrigueCSV';
        }

        if(class_exists("RegistreVCIClient") && $type == RegistreVCIClient::TYPE_MODEL) {

            return 'ExportRegistreVCICSV';
        }

        if(class_exists("ParcellaireAffectationClient") && $type == ParcellaireAffectationClient::TYPE_MODEL) {

            return 'ExportParcellaireAffectationCSV';
        }

        if(class_exists("ParcellaireIntentionAffectationClient") && $type == ParcellaireIntentionAffectationClient::TYPE_MODEL) {

            return 'ExportParcellaireIntentionAffectationCSV';
        }

        if(class_exists("DegustationClient") && $type == DegustationClient::TYPE_MODEL) {

            return 'ExportDegustationCSV';
        }

        throw new sfException(sprintf("Le type de document %s n'a pas de classe d'export correspondante", $type));
    }

    public function getExportCsvObject($doc, $header = true, $region = null) {
        $className = $this->getExportCsvClassName($doc->type);
        return new $className($doc, $header, $region);
    }

    public function getTypesAndCampagneForExport() {
        $typeAndCampagne = array();

        $rows = acCouchdbManager::getClient()
                    ->reduce(true)
                    ->group_level(2)
                    ->getView('declaration', 'export')->rows;

        foreach($rows as $row) {
            $item = new stdClass();
            $item->type = $row->key[DeclarationExportView::KEY_TYPE];
            $item->campagne = $row->key[DeclarationExportView::KEY_CAMPAGNE];
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
                    ->getView('declaration', 'export')->rows;

        foreach($rows as $row) {
            $ids[] = $row->id;
        }

        return $ids;
    }

    public function getIdsWithSearchFilter($type, $campagne, $ids_list) {
      $identifiants_etb = explode(',',$ids_list);
      $ids = array();
      foreach ($identifiants_etb as $key => $identifiant) {
        $etablissement = etablissementClient::getInstance()->find('ETABLISSEMENT-'.$identifiant);
        if(!$etablissement){
          continue;
        }
        $rows = acCouchdbManager::getClient()
                    ->startkey(array($type, $campagne,$identifiant))
                    ->endkey(array($type, $campagne,$identifiant, array()))
                    ->reduce(false)
                    ->getView('declaration', 'export')->rows;
        foreach($rows as $row) {
            $ids[] = $row->id;
        }
      }
      return $ids;
    }

    public function getIdsByIdentifiant($identifiant) {
        $ids = array();

        $rows = acCouchdbManager::getClient()
                    ->reduce(false)
                    ->getView('declaration', 'export')->rows;

        foreach($rows as $row) {
            if(str_replace("E", "", $row->key[DeclarationExportView::KEY_IDENTIFIANT]) == $identifiant) {
                $ids[] = $row->id;
            }
        }

        return $ids;
    }

    public function viewByIdentifiantCampagneAndType($identifiant, $campagne, $type) {
        $campagne .= ''; #convertion to string
        $rows = acCouchdbManager::getClient()
                        ->startkey(array($identifiant, $campagne."", $type))
                        ->endkey(array($identifiant, $campagne."", $type, array()))
                        ->reduce(false)
                        ->getView("declaration", "identifiant")
                ->rows;
        $drms = array();

        foreach ($rows as $row) {
            $drms[$row->id] = $row->key;
        }

        krsort($drms);

        return $drms;
    }
}
