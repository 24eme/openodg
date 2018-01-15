<?php

class EtablissementClient extends acCouchdbClient {

    const TYPE_MODEL = "Etablissement";
    const TYPE_COUCHDB = "ETABLISSEMENT";

    const FAMILLE_VINIFICATEUR = CompteClient::ATTRIBUT_ETABLISSEMENT_VINIFICATEUR;
    const FAMILLE_PRODUCTEUR = CompteClient::ATTRIBUT_ETABLISSEMENT_PRODUCTEUR_RAISINS;
    const FAMILLE_DISTILLATEUR = CompteClient::ATTRIBUT_ETABLISSEMENT_DISTILLATEUR;
    const FAMILLE_ELABORATEUR = CompteClient::ATTRIBUT_ETABLISSEMENT_ELABORATEUR;
    const FAMILLE_CONDITIONNEUR = CompteClient::ATTRIBUT_ETABLISSEMENT_CONDITIONNEUR;
    const FAMILLE_METTEUR_EN_MARCHE = CompteClient::ATTRIBUT_ETABLISSEMENT_METTEUR_EN_MARCHE;
    const FAMILLE_NEGOCIANT = CompteClient::ATTRIBUT_ETABLISSEMENT_NEGOCIANT;
    const FAMILLE_CAVE_COOPERATIVE = CompteClient::ATTRIBUT_ETABLISSEMENT_CAVE_COOPERATIVE;

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

    public static function getPrefixForRegion($region){
    	        $prefixs = array(CompteClient::REGION_VITICOLE => '1');
        return $prefixs[$region];
    }

    public static function cleanCivilite($nom) {
        return preg_replace("/^(M|MME|EARL|SCEA|SARL|SDF|GAEC|MLLE|SA|SAS|Mme|M\.|STEF|MEMR|MM|IND|EURL|SCA|EI|SCI|MMES|SASU|SC|SCV|Melle|ASSO|GFA)[,]? /", "", $nom);
    }
}
