<?php

class ChgtDenomClient extends acCouchdbClient {

    const TYPE_MODEL = "ChgtDenom";
    const TYPE_COUCHDB = "CHGTDENOM";
    const ORIGINE_LOT = "DREV";

    const FORMAT_DATE = 'Y-m-d\THis';


    public static function getInstance()
    {

        return acCouchdbManager::getClient("ChgtDenom");
    }

    public function find($id, $hydrate = self::HYDRATE_DOCUMENT, $force_return_ls = false) {
        $doc = parent::find($id, $hydrate, $force_return_ls);

        if($doc && $doc->type != self::TYPE_MODEL) {

            throw new sfException(sprintf("Document \"%s\" is not type of \"%s\"", $id, self::TYPE_MODEL));
        }

        return $doc;
    }

    public function findMasterByIdentifiantAndCampagne($identifiant, $campagne, $hydrate = acCouchdbClient::HYDRATE_DOCUMENT) {
        $drevs = DeclarationClient::getInstance()->viewByIdentifiantCampagneAndType($identifiant, $campagne, self::TYPE_MODEL);
        foreach ($drevs as $id => $drev) {

            return $this->find($id, $hydrate);
        }

        return null;
    }

    public function createDoc($identifiant, $date = null, $papier = false)
    {
        $chgtdenom = new ChgtDenom();
        $date = ($date && preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}$/', $date))? $date : date(self::FORMAT_DATE);

        $chgtdenom->initDoc($identifiant, $date);

        if($papier) {
            $chgtdenom->add('papier', 1);
        }

        $chgtdenom->storeDeclarant();

        return $chgtdenom;
    }

}
