<?php

class ParcellaireClient extends acCouchdbClient {

    public static function getInstance() {
        return acCouchdbManager::getClient("Parcellaire");
    }

    public function find($id, $hydrate = self::HYDRATE_DOCUMENT, $force_return_ls = false) {
        $doc = parent::find($id, $hydrate, $force_return_ls);

        if ($doc && $doc->type != self::TYPE_MODEL) {

            throw new sfException(sprintf("Document \"%s\" is not type of \"%s\"", $id, self::TYPE_MODEL));
        }

        return $doc;
    }

    public function findOrCreate($etablissement, $campagne) {
        $parcellaire = $this->find($this->buildId($etablissement->identifiant, $campagne));
        if (is_null($parcellaire)) {
            $parcellaire = $this->createDoc($etablissement->identifiant, $campagne);
        }

        return $parcellaire;
    }

    public function buildId($identifiant, $campagne) {
        return sprintf("PARCELLAIRE-%s-%s", $identifiant, $campagne);
    }

    public function createDoc($identifiant, $campagne) {
        $parcellaire = new Parcellaire();
        $parcellaire->initDoc($identifiant, $campagne);

        return $parcellaire;
    }

}
