<?php

class CompteClient extends acCouchdbClient {

    const TYPE_MODEL = "Compte";
    const TYPE_COUCHDB = "COMPTE";
    const DROIT_ADMIN = "ADMIN";
    const DROIT_OPERATEUR = "OPERATEUR";
    const TYPE_COMPTE_ETABLISSEMENT = "ETABLISSEMENT";
    const TYPE_COMPTE_AGENT_PRELEVEMENT = "AGENT_PRELEVEMENT";
    const TYPE_COMPTE_DEGUSTATEUR = "DEGUSTATEUR";
    const TYPE_COMPTE_CONTACT = "CONTACT";
    
    const STATUT_ACTIF = "ACTIF";
    
    const ATTRIBUT_ETABLISSEMENT_COOPERATEUR = "COOPERATEUR";
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
    const ATTRIBUT_AGENT_PRELEVEMENT_PRELEVEUR = "PRELEVEUR";
    const ATTRIBUT_AGENT_PRELEVEMENT_AGENT_CONTROLE = "AGENT_CONTROLE";
    const ATTRIBUT_DEGUSTATEUR_PORTEUR_MEMOIRES = "PORTEUR_MEMOIRES";
    const ATTRIBUT_DEGUSTATEUR_TECHNICIEN_PRODUIT = "TECHNICIEN_PRODUIT";
    const ATTRIBUT_DEGUSTATEUR_USAGER_PRODUIT = "USAGER_PRODUIT";
    const ATTRIBUT_CONTACT_RESTAURANT = "RESTAURANT";
    const ATTRIBUT_CONTACT_HOTEL = "HOTEL";
   // const ATTRIBUT_CONTACT_PARC_ATTRACTION = "PARC_ATTRACTION";
    
    private $libelles_attributs_etablissements = array(
        self::ATTRIBUT_ETABLISSEMENT_COOPERATEUR => 'Coopérateur',
        self::ATTRIBUT_ETABLISSEMENT_PRODUCTEUR_RAISINS => 'Producteur de raisin',
        self::ATTRIBUT_ETABLISSEMENT_CONDITIONNEUR => 'Conditionneur',
        self::ATTRIBUT_ETABLISSEMENT_VINIFICATEUR => 'Vinificateur',
        self::ATTRIBUT_ETABLISSEMENT_DONNEUR_ORDRE => 'Donneur d\'ordre',
        self::ATTRIBUT_ETABLISSEMENT_ELABORATEUR => 'Elaborateur',
        self::ATTRIBUT_ETABLISSEMENT_DISTILLATEUR => 'Distillateur',
        self::ATTRIBUT_ETABLISSEMENT_METTEUR_EN_MARCHE => 'Metteur en marché',
        self::ATTRIBUT_ETABLISSEMENT_NEGOCIANT => 'Négociant',
        self::ATTRIBUT_ETABLISSEMENT_VITICULTEUR_INDEPENDANT => 'Viticulteur indépendant',
        self::ATTRIBUT_ETABLISSEMENT_CAVE_COOPERATIVE => 'Cave coopérative'
    );
    
    private $libelles_attributs_agents_prelevement = array(
        self::ATTRIBUT_AGENT_PRELEVEMENT_PRELEVEUR => 'Prélèveur',
        self::ATTRIBUT_AGENT_PRELEVEMENT_AGENT_CONTROLE => 'Agent de contrôle');
    
    private $libelles_attributs_degustateurs = array(
        self::ATTRIBUT_DEGUSTATEUR_PORTEUR_MEMOIRES => 'Porteur de mémoire',
        self::ATTRIBUT_DEGUSTATEUR_TECHNICIEN_PRODUIT => 'Technicien du produit',
        self::ATTRIBUT_DEGUSTATEUR_USAGER_PRODUIT => 'Usager du produit');
    
    private $libelles_attributs_contacts = array(
        self::ATTRIBUT_CONTACT_RESTAURANT => 'Restaurant',
        self::ATTRIBUT_CONTACT_HOTEL => 'Hôtel'
      //  self::ATTRIBUT_CONTACT_PARC_ATTRACTION => 'Parc d\'attraction'
            );

    public static function getInstance() {
        return acCouchdbManager::getClient(self::TYPE_MODEL);
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

        return $query->execute(acCouchdbClient::HYDRATE_ARRAY)->getIds();
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
    }


    public function getAttributLibelle($compte_attribut) {
        $libellesArr = array_merge($this->libelles_attributs_etablissements, $this->libelles_attributs_degustateurs, $this->libelles_attributs_agents_prelevement, $this->libelles_attributs_contacts);
        return $libellesArr[$compte_attribut];
    }

    public function getAllTypesCompte() {
        return array(self::TYPE_COMPTE_CONTACT => self::TYPE_COMPTE_CONTACT,
            self::TYPE_COMPTE_ETABLISSEMENT => self::TYPE_COMPTE_ETABLISSEMENT,
            self::TYPE_COMPTE_DEGUSTATEUR => self::TYPE_COMPTE_DEGUSTATEUR,
            self::TYPE_COMPTE_AGENT_PRELEVEMENT => self::TYPE_COMPTE_AGENT_PRELEVEMENT);
    }

    public function getCompteTypeLibelle($type_compte) {
        $allTypesCompte = $this->getAllTypesCompteWithLibelles();
        return $allTypesCompte[$type_compte];
    }

    public function getAllTypesCompteWithLibelles() {
        return array(self::TYPE_COMPTE_CONTACT => "Contact",
            self::TYPE_COMPTE_ETABLISSEMENT => "Opérateur",
            self::TYPE_COMPTE_DEGUSTATEUR => "Dégustateur",
            self::TYPE_COMPTE_AGENT_PRELEVEMENT => "Agent de prélèvement");
    }

    public function getAllAttributsByTypeCompte() {

        $attributsByTypeCompte = array();
        foreach ($this->getAllTypesCompte() as $type_compte) {
            $attributsByTypeCompte[$type_compte] = $this->getAttributsForType($type_compte);
        }
        return $attributsByTypeCompte;
    }

}
