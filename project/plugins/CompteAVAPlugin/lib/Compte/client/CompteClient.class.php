<?php

class CompteClient extends acCouchdbClient {

    const TYPE_MODEL = "Compte";
    const TYPE_COUCHDB = "COMPTE";
    const DROIT_ADMIN = "ADMIN";
    const DROIT_TOURNEE = "TOURNEE";
    const DROIT_OPERATEUR = "OPERATEUR";
    const DROIT_CONTACT = "CONTACT";
    const DROIT_API = "API";

    const TYPE_COMPTE_ETABLISSEMENT = "ETABLISSEMENT";
    const TYPE_COMPTE_AGENT_PRELEVEMENT = "AGENT_PRELEVEMENT";
    const TYPE_COMPTE_DEGUSTATEUR = "DEGUSTATEUR";
    const TYPE_COMPTE_CONTACT = "CONTACT";
    const TYPE_COMPTE_SYNDICAT = "SYNDICAT";
    const STATUT_ACTIF = "ACTIF";
    const STATUT_INACTIF = "INACTIF";
    const ATTRIBUT_ETABLISSEMENT_APPORTEUR = "APPORTEUR";
    const ATTRIBUT_ETABLISSEMENT_PRODUCTEUR_RAISINS = "PRODUCTEUR";
    const ATTRIBUT_ETABLISSEMENT_CONDITIONNEUR = "CONDITIONNEUR";
    const ATTRIBUT_ETABLISSEMENT_VINIFICATEUR = "VINIFICATEUR";
    const ATTRIBUT_ETABLISSEMENT_DONNEUR_ORDRE = "DONNEUR_ORDRE";
    const ATTRIBUT_ETABLISSEMENT_ELABORATEUR = "ELABORATEUR";
    const ATTRIBUT_ETABLISSEMENT_DISTILLATEUR = "DISTILLATEUR";
    const ATTRIBUT_ETABLISSEMENT_METTEUR_EN_MARCHE = "METTEUR_EN_MARCHE";
    const ATTRIBUT_ETABLISSEMENT_NEGOCIANT = "NEGOCIANT";
    const ATTRIBUT_ETABLISSEMENT_VITICULTEUR_INDEPENDANT = "VITICULTEUR_INDEPENDANT";
    const ATTRIBUT_ETABLISSEMENT_CAVE_COOPERATIVE = "CAVE_COOPERATIVE";
    const ATTRIBUT_ETABLISSEMENT_ADHERENT_SYNDICAT = "ADHERENT_SYNDICAT";
    const ATTRIBUT_AGENT_PRELEVEMENT_AGENT_PRELEVEMENT = "AGENT_PRELEVEMENTS";
    const ATTRIBUT_AGENT_PRELEVEMENT_APPUI_TECHNIQUE = "APPUI_TECHNIQUE";
    const ATTRIBUT_DEGUSTATEUR_PORTEUR_MEMOIRES = "PORTEUR_MEMOIRES";
    const ATTRIBUT_DEGUSTATEUR_TECHNICIEN_PRODUIT = "TECHNICIEN_PRODUIT";
    const ATTRIBUT_DEGUSTATEUR_USAGER_PRODUIT = "USAGER_PRODUIT";

    const CHAI_ATTRIBUT_VINIFICATION = "VINIFICATION";
    const CHAI_ATTRIBUT_CONDITIONNEMENT = "CONDITIONNEMENT";
    const CHAI_ATTRIBUT_STOCKAGE = "STOCKAGE";
    const CHAI_ATTRIBUT_PRESSURAGE = "PRESSURAGE";

    const TAG_ABONNE_REVUE = "Abonné revue";

    const REGION_VITICOLE = 'ALSACE';

    const API_ADRESSE_URL = "https://api-adresse.data.gouv.fr/search/";

    private $libelles_attributs_etablissements = array(
        self::ATTRIBUT_ETABLISSEMENT_APPORTEUR => 'Producteur en structure collective',
        self::ATTRIBUT_ETABLISSEMENT_PRODUCTEUR_RAISINS => 'Producteur de raisin',
        self::ATTRIBUT_ETABLISSEMENT_CONDITIONNEUR => 'Conditionneur',
        self::ATTRIBUT_ETABLISSEMENT_VINIFICATEUR => 'Vinificateur',
        self::ATTRIBUT_ETABLISSEMENT_DONNEUR_ORDRE => 'Donneur d\'ordre',
        self::ATTRIBUT_ETABLISSEMENT_ELABORATEUR => 'Elaborateur',
        self::ATTRIBUT_ETABLISSEMENT_DISTILLATEUR => 'Distillateur',
        self::ATTRIBUT_ETABLISSEMENT_METTEUR_EN_MARCHE => 'Metteur en marché',
        self::ATTRIBUT_ETABLISSEMENT_NEGOCIANT => 'Négociant',
        self::ATTRIBUT_ETABLISSEMENT_VITICULTEUR_INDEPENDANT => 'Viticulteur indépendant',
        self::ATTRIBUT_ETABLISSEMENT_CAVE_COOPERATIVE => 'Cave coopérative',
        self::ATTRIBUT_ETABLISSEMENT_ADHERENT_SYNDICAT => 'Adhérent au syndicat'
    );
    private $libelles_attributs_agents_prelevement = array(
        self::ATTRIBUT_AGENT_PRELEVEMENT_AGENT_PRELEVEMENT => 'Agent de prélèvement',
        self::ATTRIBUT_AGENT_PRELEVEMENT_APPUI_TECHNIQUE => 'Appui technique'
    );
    private $libelles_attributs_degustateurs = array(
        self::ATTRIBUT_DEGUSTATEUR_PORTEUR_MEMOIRES => 'Porteur de mémoire',
        self::ATTRIBUT_DEGUSTATEUR_TECHNICIEN_PRODUIT => 'Technicien du produit',
        self::ATTRIBUT_DEGUSTATEUR_USAGER_PRODUIT => 'Usager du produit'
    );
    private $libelles_attributs_contacts = array(
    );
    private $libelles_attributs_syndicats = array(
    );

    private $libelles_chais_attributs = array(
        self::CHAI_ATTRIBUT_VINIFICATION => "Chai de vinification",
        self::CHAI_ATTRIBUT_CONDITIONNEMENT => "Centre de conditionnement",
        self::CHAI_ATTRIBUT_STOCKAGE => "Lieu de stockage",
        self::CHAI_ATTRIBUT_PRESSURAGE => "Centre de pressurage",
    );

    public static function getInstance() {
        return acCouchdbManager::getClient(self::TYPE_MODEL);
    }

    public function findByLogin($identifiant) {

        return $this->findByIdentifiant($identifiant);
    }

    public function findByIdentifiant($identifiant) {

        return $this->find(self::TYPE_COUCHDB . '-' . $identifiant);
    }

    public function getAll($hydrate = self::HYDRATE_DOCUMENT) {

        $query = $this->startkey(sprintf("COMPTE-%s", "aaaaaaaaaaaa"))
                ->endkey(sprintf("COMPTE-%s", "zzzzzzzzzzzz"));

        return $query->execute(acCouchdbClient::HYDRATE_ARRAY);
    }

    public function getAllComptesPrefixedIds($prefix, $hydrate = self::HYDRATE_DOCUMENT) {

        $query = $this->startkey(sprintf("COMPTE-" . $prefix . "%s", "000000"))
                ->endkey(sprintf("COMPTE-" . $prefix . "%s", "999999"));

        return $query->execute(acCouchdbClient::HYDRATE_ON_DEMAND)->getIds();
    }

    public function getAllComptesPrefixed($prefix, $hydrate = self::HYDRATE_JSON) {

        $query = $this->startkey(sprintf("COMPTE-" . $prefix . "%s", "000000"))
                ->endkey(sprintf("COMPTE-" . $prefix . "%s", "999999"));

        return $query->execute(acCouchdbClient::HYDRATE_JSON);
    }

    public function getAllSyndicats() {
        return $this->getAllComptesPrefixedIds('S');
    }

    public function find($id, $hydrate = self::HYDRATE_DOCUMENT, $force_return_ls = false) {
        $doc = parent::find($id, $hydrate, $force_return_ls);

        if ($doc && $doc->type != self::TYPE_MODEL) {

            throw new sfException("Document \"%s\" is not type of \"%s\"", $id, self::TYPE_MODEL);
        }

        return $doc;
    }

    public function createIdentifiantForCompte($compte) {
        if (!$compte->isNew()) {
            throw new sfException("Le compte doit être un nouveau compte");
        }
        $type_compte = $compte->getTypeCompte();
        if (!$type_compte) {
            throw new sfException("Le compte doit avoir un type de compte");
        }
        $prefixForIdentifiant = $this->getPrefix($type_compte);
        if ($type_compte == self::TYPE_COMPTE_ETABLISSEMENT) {
            if (!$compte->cvi) {
                throw new sfException("Les etablissements doivent être spécifiés avec le cvi! Pour le moment en tout cas");
            }
            return $prefixForIdentifiant . $compte->cvi;
        } else {
            return $this->getNextIdentifiantForIncrementCompte($prefixForIdentifiant);
        }
    }

    public function getPrefix($type_compte) {
        if ($type_compte == self::TYPE_COMPTE_ETABLISSEMENT) {
            return "E";
        }
        if ($type_compte == self::TYPE_COMPTE_CONTACT) {
            return "C";
        }
        if ($type_compte == self::TYPE_COMPTE_DEGUSTATEUR) {
            return "D";
        }
        if ($type_compte == self::TYPE_COMPTE_AGENT_PRELEVEMENT) {
            return "A";
        }
        if ($type_compte == self::TYPE_COMPTE_SYNDICAT) {
            return "S";
        }
        throw new sfException(sprintf("Ce type de compte %s n'est pas incrémental", $type_compte));
    }

    public function getNextIdentifiantForIncrementCompte($prefixForIdentifiant) {
        $comptesIds = $this->getAllComptesPrefixedIds($prefixForIdentifiant);
        $last_num = 0;
        foreach ($comptesIds as $id) {
            if (!preg_match('/COMPTE-' . $prefixForIdentifiant . '([0-9]{6})/', $id, $matches)) {
                continue;
            }

            $num = $matches[1];
            if ($num > $last_num) {
                $last_num = $num;
            }
        }

        return sprintf($prefixForIdentifiant . "%06d", $last_num + 1);
    }

    public function getAttributsForType($type_compte) {
        if ($type_compte == self::TYPE_COMPTE_CONTACT) {
            return $this->libelles_attributs_contacts;
        }
        if ($type_compte == self::TYPE_COMPTE_DEGUSTATEUR) {
            return $this->libelles_attributs_degustateurs;
        }
        if ($type_compte == self::TYPE_COMPTE_AGENT_PRELEVEMENT) {
            return $this->libelles_attributs_agents_prelevement;
        }
        if ($type_compte == self::TYPE_COMPTE_ETABLISSEMENT) {
            return $this->libelles_attributs_etablissements;
        }
        if ($type_compte == self::TYPE_COMPTE_SYNDICAT) {
            return $this->libelles_attributs_syndicats;
        }
    }

    public function getAttributLibelle($compte_attribut) {
        $libellesArr = array_merge($this->libelles_attributs_etablissements, $this->libelles_attributs_degustateurs, $this->libelles_attributs_agents_prelevement, $this->libelles_attributs_contacts,$this->libelles_attributs_syndicats);
        return $libellesArr[$compte_attribut];
    }

    public function getChaiAttributLibelles() {

        return $this->libelles_chais_attributs;
    }

    public function getChaiAttributLibelle($attribut) {

        return $this->libelles_chais_attributs[$attribut];
    }

    public function getComptes($query) {
        $qs = new acElasticaQueryQueryString($query);
        $q = new acElasticaQuery();
        $q->setQuery($qs);
        $q->setLimit(99999);

        $index = acElasticaManager::getType('COMPTE');
        $resset = $index->search($q);

        return $resset->getResults();
    }

    public function getAllTypesCompte() {
        return array(
            self::TYPE_COMPTE_ETABLISSEMENT => self::TYPE_COMPTE_ETABLISSEMENT,
            self::TYPE_COMPTE_CONTACT => self::TYPE_COMPTE_CONTACT,
            self::TYPE_COMPTE_DEGUSTATEUR => self::TYPE_COMPTE_DEGUSTATEUR,
            self::TYPE_COMPTE_AGENT_PRELEVEMENT => self::TYPE_COMPTE_AGENT_PRELEVEMENT,
            self::TYPE_COMPTE_SYNDICAT => self::TYPE_COMPTE_SYNDICAT);
    }

    public function getCompteTypeLibelle($type_compte) {
        $allTypesCompte = $this->getAllTypesCompteWithLibelles();
        return $allTypesCompte[$type_compte];
    }

    public function getAllTypesCompteWithLibelles() {
        return array(self::TYPE_COMPTE_CONTACT => "Contact",
        self::TYPE_COMPTE_ETABLISSEMENT => "Opérateur",
        self::TYPE_COMPTE_DEGUSTATEUR => "Dégustateur",
        self::TYPE_COMPTE_AGENT_PRELEVEMENT => "Agent de prélèvement",
        self::TYPE_COMPTE_SYNDICAT => "Syndicat");
    }

    public function getAllAttributsByTypeCompte() {

        $attributsByTypeCompte = array();
        foreach ($this->getAllTypesCompte() as $type_compte) {
            $attributsByTypeCompte[$type_compte] = $this->getAttributsForType($type_compte);
        }
        return $attributsByTypeCompte;
    }

    public function makeLibelle($compte) {
        if(is_array($compte)) {
            $compte = (object) $compte;
        }

        return sprintf("%s (%s) à %s (%s)", $compte->nom_a_afficher, ($compte->cvi) ? $compte->cvi : (($compte->siren) ? $compte->siren : $compte->identifiant_interne), $compte->commune, $compte->code_postal);
    }

    public function calculCoordonnees($adresse, $commune, $code_postal) {
        $adresse = trim(preg_replace("/B[\.]*P[\.]* [0-9]+/", "", $adresse));
        $url = CompteClient::API_ADRESSE_URL.'?q='.urlencode($adresse." ".$commune."&postcode=".$code_postal."&type=housenumber");

        $file = file_get_contents($url);
        $result = json_decode($file);
        if(!$result || !count($result->features)){
            return false;
        }
        if(KeyInflector::slugify($result->features[0]->properties->city) != KeyInflector::slugify($commune)) {
            //echo sprintf("WARNING;Commune différent %s / %s;%s\n", $result->response->docs[0]->commune, $commune, $this->_id);
        }
        return array("lat" => $result->features[0]->geometry->coordinates[1], "lon" => $result->features[0]->geometry->coordinates[0]);
    }


}
