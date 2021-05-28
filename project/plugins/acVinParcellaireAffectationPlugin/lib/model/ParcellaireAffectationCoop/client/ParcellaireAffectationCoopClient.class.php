<?php

class ParcellaireAffectationCoopClient extends acCouchdbClient {

    const TYPE_MODEL = "ParcellaireAffectationCoop";
    const TYPE_COUCHDB = "PARCELLAIREAFFECTATIONCOOP";

    public static function getInstance()
    {
      return acCouchdbManager::getClient("ParcellaireAffectationCoop");
    }

    public function findOrCreate($identifiant, $periode, $papier = false, $type = self::TYPE_COUCHDB) {
        if (strlen($periode) != 4)
            throw new sfException("La periode doit être une année et non " . $periode);
        $parcellaireAffectationCoop = $this->find($this->buildId($identifiant, $periode, $type));
        if (is_null($parcellaireAffectationCoop)) {
            $parcellaireAffectationCoop = $this->createDoc($identifiant, $periode, $papier, $type);
        }

        return $parcellaireAffectationCoop;
    }

    public function buildId($identifiant, $periode, $type = self::TYPE_COUCHDB) {
        $id = "$type-%s-%s";
        return sprintf($id, $identifiant, $periode);
    }

    public function createDoc($identifiant, $periode, $type = self::TYPE_COUCHDB) {
        $parcellaireAffectationCoop = new ParcellaireAffectationCoop();
        $parcellaireAffectationCoop->initDoc($identifiant, $periode, $type);

        $sv11 = SV11Client::getInstance()->find("SV11-".$identifiant."-".$periode);

        $parcellaireAffectationCoop->buildApporteurs($sv11);

        return $parcellaireAffectationCoop;
    }

    public function getHistory($identifiant, $max_annee = '9999', $hydrate = acCouchdbClient::HYDRATE_DOCUMENT) {
        $periode_from = "0000";
        $periode_to = $max_annee;

        $id = self::TYPE_COUCHDB.'-%s-%s';
        return $this->startkey(sprintf($id, $identifiant, $periode_from))
        ->endkey(sprintf($id, $identifiant, $periode_to))
        ->execute($hydrate);
    }

}
