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

    public static $chaisAttributsInImport = array(); //Bouchon pour les taches d'imports

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

        $query = $this->startkey("ETABLISSEMENT-")
                    ->endkey("ETABLISSEMENT-ZZZZZZZZZ");

        return $query->execute(acCouchdbClient::HYDRATE_ARRAY);
    }

    public function findByCvi($identifiant) {
        return $this->findByIdentifiant($identifiant);
    }

    public function findByIdentifiant($identifiant) {

        return $this->find('ETABLISSEMENT-'.$identifiant);
    }

    /**
     * Rechercher un établissment par id, identifiant, cvi, no accices, ppm
     *
     * @param string $anyIdentifiant Id, identifiant, cvi, no accices, ppm
     * @param bool $withSuspendu Inclure les établissements suspendu
     *
     * @return Etablissement
     */
    public function findAny($anyIdentifiant) {
        $etablissement = $this->find($this->getId($anyIdentifiant));

        if($etablissement) {

            return $etablissement;
        }

        return $this->findByCvi($anyIdentifiant);
    }

    public function getId($id_or_identifiant) {
        $id = $id_or_identifiant;
        if (strpos($id_or_identifiant, 'ETABLISSEMENT-') === false) {
            $id = 'ETABLISSEMENT-' . $id_or_identifiant;
        }

        return $id;
    }

    public function findByCviOrAcciseOrPPMOrSiren($cvi_or_accise_or_ppm, $with_suspendu = false){
        $qs = new acElasticaQueryQueryString($cvi_or_accise_or_ppm);
        $q = new acElasticaQuery();
        $q->setQuery($qs);
        $index = acElasticaManager::getType('COMPTE');
        $resset = $index->search($q);
        $results = $resset->getResults();
        foreach ($results as $res) {
            $data = $res->getData()['doc'];
            if ($data['type_compte'] != 'ETABLISSEMENT') {
                continue;
            }
            return $this->find($data['etablissement']);
        }
        return null;
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
