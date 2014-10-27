<?php

class EtablissementClient extends acCouchdbClient {
    
    const TYPE_MODEL = "Etablissement"; 
    const TYPE_COUCHDB = "ETABLISSEMENT";

    const FAMILLE_VINIFICATEUR = "VINIFICATEUR";
    const FAMILLE_PRODUCTEUR = "PRODUCTEUR";
    const FAMILLE_DISTILLATEUR = "DISTILLATEUR";
    const FAMILLE_ELABORATEUR = "ELABORATEUR";
    const FAMILLE_CONDITIONNEUR = "CONDITIONNEUR";
    const FAMILLE_METTEUR_EN_MARCHE = "METTEUR_EN_MARCHE";
    const FAMILLE_NEGOCIANT = "NEGOCIANT";
    const FAMILLE_CAVE_COOPERATIVE = "CAVE_COOPERATIVE";

    const STATUT_INSCRIT = 'INSCRIT';

    public static function getInstance()
    {
        return acCouchdbManager::getClient(self::TYPE_MODEL);
    }

    public function find($id, $hydrate = self::HYDRATE_DOCUMENT, $force_return_ls = false) {
        $doc = parent::find($id, $hydrate, $force_return_ls);

        if($doc && $doc->type != self::TYPE_MODEL) {

            throw new sfException("Document \"%s\" is not type of \"%s\"", $id, self::TYPE_MODEL);
        }

        return $doc;
    }

    public function getAll($hydrate = self::HYDRATE_DOCUMENT) {

        $query = $this->startkey(sprintf("ETABLISSEMENT-%s", "0000000000"))
                    ->endkey(sprintf("ETABLISSEMENT-%s", "9999999999"));
        
        return $query->execute(acCouchdbClient::HYDRATE_ARRAY);
    }

    public function findByIdentifiant($identifiant) {

        return $this->find('ETABLISSEMENT-'.$identifiant);
    }

    public function createOrFind($identifiant) {
        $doc = $this->findByIdentifiant($identifiant);

        if($doc) {

            return $doc;
        }

        return $this->createDoc($identifiant);
    }

    public function createDoc($identifiant) {
        $doc = new Etablissement();
        $doc->identifiant = $identifiant;

        return $doc;
    }
}
