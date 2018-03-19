<?php

class HabilitationClient extends acCouchdbClient {

    const TYPE_MODEL = "Habilitation";
    const TYPE_COUCHDB = "HABILITATION";

    const ACTIVITE_PRODUCTEUR = "PRODUCTEUR";
    const ACTIVITE_VINIFICATEUR = "VINIFICATEUR";
    const ACTIVITE_VRAC = "VRAC";
    const ACTIVITE_ELABORATEUR = "ELABORATEUR";
    const ACTIVITE_CONDITIONNEUR = "CONDITIONNEUR";
    const ACTIVITE_VENTE_A_LA_TIREUSE = "VENTE_A_LA_TIREUSE";


    const STATUT_DEMANDE_HABILITATION = "DEMANDE_HABILITATION";
    const STATUT_DEMANDE_RETRAIT = "DEMANDE_RETRAIT";
    const STATUT_HABILITE = "HABILITE";
    const STATUT_SUSPENDU = "SUSPENDU";
    const STATUT_REFUS = "REFUS";
    const STATUT_RETRAIT = "RETRAIT";
    const STATUT_ANNULE = "ANNULÉ";

    public static $activites_libelles = array( self::ACTIVITE_PRODUCTEUR => "Producteur",
                                                  self::ACTIVITE_VINIFICATEUR => "Vinificateur",
                                                  self::ACTIVITE_VRAC => "Vrac",
                                                  self::ACTIVITE_CONDITIONNEUR => "Conditionneur",
                                                  self::ACTIVITE_ELABORATEUR => "Élaborateur",
                                                  self::ACTIVITE_VENTE_A_LA_TIREUSE => "Vente tireuse"
                                                );
    public static $statuts_libelles = array( self::STATUT_DEMANDE_HABILITATION => "Demande d'habilitation",
                                             self::STATUT_DEMANDE_RETRAIT => "Demande de retrait",
                                             self::STATUT_HABILITE => "Habilité",
                                             self::STATUT_SUSPENDU => "Suspendu",
                                             self::STATUT_REFUS => "Refus",
                                             self::STATUT_ANNULE => "Annulé",
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

        public function createDoc($identifiant, $date = '') {
          if (!$date) {
            $date = date('Y-m-d');
          }
          return $this->createOrGetDocFromIdentifiantAndDate($identifiant, $date);
        }

        public function createOrGetDocFromIdentifiantAndDate($identifiant, $date)
        {
            $habilitation_found = $this->findPreviousByIdentifiantAndDate($identifiant, $date);
            if ($habilitation_found && $habilitation_found->date === $date) {
              return $habilitation_found;
            }
            if (!$habilitation_found) {
              $habilitation = new Habilitation();
              $habilitation->initDoc($identifiant,$date);
              $etablissement = $habilitation->getEtablissementObject();
            }else{
              $habilitation_found->date = $date;
              $habilitation = clone $habilitation_found;
              $habilitation_found = null;
            }

            return $habilitation;
        }

        public function findPreviousByIdentifiantAndDate($identifiant, $date, $hydrate = acCouchdbClient::HYDRATE_DOCUMENT) {
          $h = $this->getHistory($identifiant, $date, $hydrate);
          if (!count($h)) {
            return NULL;
          }
          $h = $h->getDocs();
          end($h);
          $doc = $h[key($h)];
          return $doc;
        }

        public function findNextByIdentifiantAndDate($identifiant, $date, $hydrate = acCouchdbClient::HYDRATE_DOCUMENT) {
            $h = $this->getHistory($identifiant, '9999-99-99', acCouchdbClient::HYDRATE_JSON, $date);
            $docs = $h->getDocs();
            foreach($docs as $doc) {
                if($doc->date > $date) {

                    return $this->find($doc->_id, $hydrate);
                }
            }

            return null;
        }

        public function getHistory($identifiant, $date = '9999-99-99', $hydrate = acCouchdbClient::HYDRATE_DOCUMENT, $dateDebut = "0000-00-00") {

            return $this->startkey(sprintf(self::TYPE_COUCHDB."-%s-%s", $identifiant, str_replace('-', '', $dateDebut)))
                        ->endkey(sprintf(self::TYPE_COUCHDB."-%s-%s", $identifiant, str_replace('-', '', $date)))->execute($hydrate);
        }

        public function getLastHabilitation($identifiant, $hydrate = acCouchdbClient::HYDRATE_DOCUMENT){
            $history = $this->getHistory($identifiant, $hydrate);

            return $this->findPreviousByIdentifiantAndDate($identifiant, '9999-99-99');
        }

        public function getLastHabilitationOrCreate($identifiant, $hydrate = acCouchdbClient::HYDRATE_DOCUMENT){
            $habilitation = $this->getLastHabilitation($identifiant);

            if(!$habilitation) {

                $habilitation = $this->createDoc($identifiant);
            }

            return $habilitation;
        }

        public function getAllEtablissementsWithHabilitations($hydrate = acCouchdbClient::HYDRATE_DOCUMENT){
          $allHabilitations = $this->startkey(self::TYPE_COUCHDB."-00000000-00000000")
                      ->endkey(self::TYPE_COUCHDB."-99999999-99999999")->execute($hydrate);
          $etbIds = array();
          foreach ($allHabilitations as $habilitation) {
            $etbIds[$habilitation->getIdentifiant()] = $habilitation->getIdentifiant();
          }
          krsort($etbIds);
          return array_unique($etbIds);
        }

        public function updateAndSaveHabilitation($etablissementIdentifiant, $hash_produit, $date, $activites, $statut, $commentaire = "") {
            $last = HabilitationClient::getInstance()->getLastHabilitation($etablissementIdentifiant);
            $habilitation = HabilitationClient::getInstance()->findPreviousByIdentifiantAndDate($etablissementIdentifiant, $date);

            if($habilitation->_id < $last->_id) {
                foreach($activites as $activiteKey) {
                    if(!$last->exist($hash_produit)) {
                        continue;
                    }

                    if(!$habilitation->exist($hash_produit)) {
                        continue;
                    }

                    if(!$last->get($hash_produit)->activites->exist($activiteKey)) {
                        continue;
                    }

                    if(!$habilitation->get($hash_produit)->activites->exist($activiteKey)) {
                        continue;
                    }

                    $activiteLast = $last->get($hash_produit)->activites->get($activiteKey);
                    $activite = $habilitation->get($hash_produit)->activites->get($activiteKey);

                    if($activiteLast->statut == $activite->statut && $activiteLast->date == $activite->date) {
                        continue;
                    }

                    throw new sfException("Une habilitation différente avec une date supérieur existe déjà");
                }
            }

            $habilitation = $this->createOrGetDocFromIdentifiantAndDate($etablissementIdentifiant, $date);
            $habilitation->updateHabilitation($hash_produit, $activites, $statut, $commentaire, $date);
            $habilitation->save();

            $dateCourante = $date;
            while($habilitationSuivante = $this->findNextByIdentifiantAndDate($etablissementIdentifiant, $dateCourante)) {
                if(!$habilitationSuivante || $habilitationSuivante->_id <= $habilitation->_id) {
                    break;
                }

                $habilitationSuivante->updateHabilitation($hash_produit, $activites, $statut, $commentaire, $date);
                $habilitationSuivante->save();
                $dateCourante = $habilitationSuivante->date;
            }
        }
    }
