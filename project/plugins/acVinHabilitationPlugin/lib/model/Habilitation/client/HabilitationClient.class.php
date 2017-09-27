<?php

class HabilitationClient extends acCouchdbClient {

    const TYPE_MODEL = "Habilitation";
    const TYPE_COUCHDB = "HABILITATION";

    const ACTIVITE_PRODUCTEUR = "PRODUCTEUR";
    const ACTIVITE_VINIFICATEUR = "VINIFICATEUR";
    const ACTIVITE_VRAC = "VRAC";
    const ACTIVITE_CONDITIONNEUR = "CONDITIONNEUR";
    const ACTIVITE_VENTE_A_LA_TIREUSE = "VENTE_A_LA_TIREUSE";


    const STATUT_DEMANDE_ODG = "STATUT_DEMANDE_ODG";
    const STATUT_DEMANDE_INAO = "STATUT_DEMANDE_INAO";
    const STATUT_HABILITE = "HABILITE";
    const STATUT_SUSPENDU = "SUSPENTU";
    const STATUT_REFUS = "REFUS";
    const STATUT_RETRAIT = "RETRAIT";

    public static $activites_libelles = array( self::ACTIVITE_PRODUCTEUR => "Producteur",
                                                  self::ACTIVITE_VINIFICATEUR => "Vinificateur",
                                                  self::ACTIVITE_VRAC => "Vrac",
                                                  self::ACTIVITE_CONDITIONNEUR => "Conditionneur",
                                                  self::ACTIVITE_VENTE_A_LA_TIREUSE => "Vente tireuse",
                                                );
    public static $statuts_libelles = array( self::STATUT_DEMANDE_ODG => "Demande ODG",
                                               self::STATUT_DEMANDE_INAO =>  "Demande INAO",
                                               self::STATUT_HABILITE => "Habilité",
                                               self::STATUT_SUSPENDU => "Suspendu",
                                               self::STATUT_REFUS => "Refus",
                                               self::STATUT_RETRAIT => "Retrait");

    public static function getInstance()
    {
      return acCouchdbManager::getClient("Habilitation");
    }

        public function find($id, $hydrate = self::HYDRATE_DOCUMENT, $force_return_ls = false) {
            $doc = parent::find($id, $hydrate, $force_return_ls);

            if($doc && $doc->type != self::TYPE_MODEL) {

                throw new sfException(sprintf("Document \"%s\" is not type of \"%s\"", $id, self::TYPE_MODEL));
            }

            return $doc;
        }

        public function findMasterByIdentifiant($identifiant, $hydrate = acCouchdbClient::HYDRATE_DOCUMENT) {
            $habilitations = DeclarationClient::getInstance()->viewByIdentifiantCampagneAndType($identifiant, null, self::TYPE_MODEL);
            foreach ($habilitations as $id => $habilitation) {

                return $this->find($id, $hydrate);
            }

            return null;
        }

        public function createDoc($identifiant,$date)
        {
            $habilitation = new Habilitation();
            $habilitation->initDoc($identifiant,$date);

            $habilitation->storeDeclarant();

            $etablissement = $habilitation->getEtablissementObject();

            if(!$etablissement->hasFamille(EtablissementFamilles::FAMILLE_PRODUCTEUR)) {
                $habilitation->add('non_recoltant', 1);
            }

            if(!$etablissement->hasFamille(EtablissementFamilles::FAMILLE_CONDITIONNEUR)) {
                $habilitation->add('non_conditionneur', 1);
            }

            return $habilitation;
        }


        public function getHistory($identifiant, $hydrate = acCouchdbClient::HYDRATE_DOCUMENT) {
            return $this->startkey(sprintf(self::TYPE_COUCHDB."-%s", $identifiant))
                        ->endkey(sprintf(self::TYPE_COUCHDB."-%s_ZZZZZZZZZZZZZZ", $identifiant))
                        ->execute($hydrate);
        }
    }
