<?php

class CompteClient extends acCouchdbClient {

    const TYPE_MODEL = "Compte";
    const TYPE_COUCHDB = "COMPTE";
    const DROIT_ADMIN = "ADMIN";
    const DROIT_OPERATEUR = "OPERATEUR";
    const TYPE_COMPTE_PERSONNE = "PERSONNE";
    const TYPE_COMPTE_ETABLISSEMENT = "ETABLISSEMENT";
    
    const ATTRIBUT_COMPTE_DIVER = "COMPTE_DIVER";
    const ATTRIBUT_COMPTE_AGENT_PRELEVEMENT = "COMPTE_AGENT_PRELEVEMENT";
    const ATTRIBUT_COMPTE_OPERATEUR = "COMPTE_OPERATEUR";
    const ATTRIBUT_COMPTE_DEGUSTATEUR = "COMPTE_DEGUSTATEUR";
    
    private $libelles_attributs_compte = array(self::ATTRIBUT_COMPTE_DIVER => 'Divers',
        self::ATTRIBUT_COMPTE_AGENT_PRELEVEMENT => 'Agent de Prélèvement',
        self::ATTRIBUT_COMPTE_OPERATEUR => 'Opérateur',
        self::ATTRIBUT_COMPTE_DEGUSTATEUR => 'Dégustateur');

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

    public function getAllComptesPersonneIds($hydrate = self::HYDRATE_DOCUMENT) {

        $queryPersonnes = $this->startkey(sprintf("COMPTE-P%s", "000000"))
                ->endkey(sprintf("COMPTE-P%s", "999999"));

        $comptePersonnes = $queryPersonnes->execute(acCouchdbClient::HYDRATE_ARRAY);

        return $comptePersonnes->getIds();
    }

    public function find($id, $hydrate = self::HYDRATE_DOCUMENT, $force_return_ls = false) {
        $doc = parent::find($id, $hydrate, $force_return_ls);

        if ($doc && $doc->type != self::TYPE_MODEL) {

            throw new sfException("Document \"%s\" is not type of \"%s\"", $id, self::TYPE_MODEL);
        }

        return $doc;
    }

    public function getNextIdentifiant() {
        $comptesIds = $this->getAllComptesPersonneIds();
        $last_num = 0;
        foreach ($comptesIds as $id) {
            if (!preg_match('/COMPTE-P([0-9]{6})/', $id, $matches)) {
                continue;
            }

            $num = $matches[1];
            if ($num > $last_num) {
                $last_num = $num;
            }
        }

        return sprintf("P%06d", $last_num + 1);
    }

    public function getAttributsForType($type_compte) {
        if ($type_compte == self::TYPE_COMPTE_PERSONNE) {
            return array(self::ATTRIBUT_COMPTE_DEGUSTATEUR => self::ATTRIBUT_COMPTE_DEGUSTATEUR,
                self::ATTRIBUT_COMPTE_AGENT_PRELEVEMENT => self::ATTRIBUT_COMPTE_AGENT_PRELEVEMENT,
                self::ATTRIBUT_COMPTE_OPERATEUR => self::ATTRIBUT_COMPTE_OPERATEUR,
                self::ATTRIBUT_COMPTE_DIVER => self::ATTRIBUT_COMPTE_DIVER);
        }
        if ($type_compte == self::TYPE_COMPTE_ETABLISSEMENT) {
            return array(EtablissementClient::FAMILLE_CAVE_COOPERATIVE => EtablissementClient::FAMILLE_CAVE_COOPERATIVE,
                EtablissementClient::FAMILLE_CONDITIONNEUR => EtablissementClient::FAMILLE_CONDITIONNEUR,
                EtablissementClient::FAMILLE_DISTILLATEUR => EtablissementClient::FAMILLE_DISTILLATEUR,
                EtablissementClient::FAMILLE_ELABORATEUR => EtablissementClient::FAMILLE_ELABORATEUR,
                EtablissementClient::FAMILLE_METTEUR_EN_MARCHE => EtablissementClient::FAMILLE_METTEUR_EN_MARCHE,
                EtablissementClient::FAMILLE_NEGOCIANT => EtablissementClient::FAMILLE_NEGOCIANT,
                EtablissementClient::FAMILLE_PRODUCTEUR => EtablissementClient::FAMILLE_PRODUCTEUR,
                EtablissementClient::FAMILLE_VINIFICATEUR => EtablissementClient::FAMILLE_VINIFICATEUR);
        }
    }
    
     public function getAttributLibelle($compte_attribut) {         
        $libellesArr = array_merge($this->libelles_attributs_compte, EtablissementClient::getInstance()->getAllLibellesAttributs());
        return $libellesArr[$compte_attribut];
    }

}
