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

    const ACTIVITE_PRODUCTEUR_MOUTS = "PRODUCTEUR_MOUTS";
    const ACTIVITE_ELEVEUR_DGC = "ELEVEUR_DGC";


    const STATUT_DEMANDE_HABILITATION = "DEMANDE_HABILITATION";
    const STATUT_ATTENTE_HABILITATION = "ATTENTE_HABILITATION";
    const STATUT_DEMANDE_RETRAIT = "DEMANDE_RETRAIT";
    const STATUT_HABILITE = "HABILITE";
    const STATUT_SUSPENDU = "SUSPENDU";
    const STATUT_REFUS = "REFUS";
    const STATUT_RETRAIT = "RETRAIT";
    const STATUT_ANNULE = "ANNULÉ";

    const DEMANDE_HABILITATION = "HABILITATION";
    const DEMANDE_RETRAIT = "RETRAIT";
    const DEMANDE_DECLARANT = "DECLARANT";

    const STATUT_ARCHIVE = "ARCHIVE";

    public static $demandes_produit = array(
        self::DEMANDE_HABILITATION,
        self::DEMANDE_RETRAIT,
    );

    public static $demandes_declarant = array(
        self::DEMANDE_DECLARANT,
    );

    public static $demande_libelles = array(
        self::DEMANDE_HABILITATION => "Habilitation",
        self::DEMANDE_RETRAIT => "Retrait",
        self::DEMANDE_DECLARANT => "Changement d'identification",
    );

    public static $demande_statut_libelles = array(
        'DEPOT' => "Dépôt",
        'RELANCE_1' => "Relance n°1",
        'RELANCE_2' => "Relance n°2",
        'RELANCE_3' => "Relance n°3",
        'COMPLET' => "Complet",
        'TRANSMIS_CI' => "Transmis au Contrôle Interne",
        'VALIDE_CI' => "Validé par le Contrôle Interne",
        'REFUSE_CI' => "Refusé par le Contrôle Interne",
        'TRANSMIS_OIVR' => "Transmis à l'OIVR",
        'VALIDE_OIVR' => "Validé par l'OIVR",
        'REFUSE_OIVR' => "Refusé par l'OIVR",
        'TRANSMIS_CERTIPAQ' => "Transmis à CERTIPAQ",
        'VALIDE_CERTIPAQ' => "Validé par CERTIPAQ",
        'REFUSE_CERTIPAQ' => "Refusé par CERTIPAQ",
        'TRANSMIS_ODG' => "Transmis à l'ODG",
        'VALIDE_ODG' => "Validé par l'ODG",
        'REFUSE_ODG' => "Refusé par l'ODG",
        'TRANSMIS_INAO' => "Transmis à l'INAO",
        'VALIDE_INAO' => "Validé par l'INAO",
        'REFUSE_INAO' => "Refusé par l'INAO",
        'VALIDE' => "Validé",
        'REFUSE' => "Refusé",
    );

    public static $activites_libelles = array(
      /*
       self::ACTIVITE_PRODUCTEUR => "Producteur de raisins",
       */
                                                  self::ACTIVITE_PRODUCTEUR => "Producteur",
                                                  self::ACTIVITE_VINIFICATEUR => "Vinificateur",
                                                  self::ACTIVITE_VRAC => "Détenteur de vin en vrac",
                                                  self::ACTIVITE_CONDITIONNEUR => "Conditionneur",
                                                  self::ACTIVITE_ELABORATEUR => "Élaborateur",
                                                  self::ACTIVITE_VENTE_A_LA_TIREUSE => "Vente tireuse"
                                                  ,self::ACTIVITE_PRODUCTEUR_MOUTS => "Producteur de moût"
                                                  ,self::ACTIVITE_ELEVEUR_DGC => "Eleveur de DGC"
                                                );
    public static $activites_libelles_to_be_sorted = array( self::ACTIVITE_PRODUCTEUR => "01_Producteur de raisins",
                                                  self::ACTIVITE_VINIFICATEUR => "03_Vinificateur",
                                                  self::ACTIVITE_VRAC => "05_Détenteur de vin en vrac",
                                                  self::ACTIVITE_CONDITIONNEUR => "06_Conditionneur",
                                                  self::ACTIVITE_ELABORATEUR => "99_Élaborateur",
                                                  self::ACTIVITE_VENTE_A_LA_TIREUSE => "99_Vente tireuse"
                                                  ,self::ACTIVITE_PRODUCTEUR_MOUTS => "02_Producteur de moût"
                                                  ,self::ACTIVITE_ELEVEUR_DGC => "04_Eleveur de DGC"
                                                );
    public static $statuts_libelles = array( self::STATUT_DEMANDE_HABILITATION => "Demande d'habilitation",
                                             self::STATUT_ATTENTE_HABILITATION => "En attente d'habilitation",
                                             self::STATUT_DEMANDE_RETRAIT => "Demande de retrait",
                                             self::STATUT_HABILITE => "Habilité",
                                             self::STATUT_SUSPENDU => "Suspendu",
                                             self::STATUT_REFUS => "Refus",
                                             self::STATUT_ANNULE => "Annulé",
                                             self::STATUT_RETRAIT => "Retrait",
                                            self::STATUT_ARCHIVE => "Archivé");

    public static function getInstance()
    {
      return acCouchdbManager::getClient("Habilitation");
    }

    public function getLibelleActiviteToBeSorted($key) {

        if(!isset(self::$activites_libelles_to_be_sorted[$key])) {

            return $key;
        }

        return self::$activites_libelles_to_be_sorted[$key];
    }


    public function getLibelleActivite($key) {

        if(!isset(self::$activites_libelles[$key])) {

            return $key;
        }

        return self::$activites_libelles[$key];
    }

    public function getLibelleStatut($key) {

        if(isset(self::$statuts_libelles[$key])) {

            return self::$statuts_libelles[$key];
        }

        if(isset(self::$demande_statut_libelles[$key])) {

            return self::$demande_statut_libelles[$key];
        }

        return $key;
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
            return $this->findPreviousByIdentifiantAndDate($identifiant, '9999-99-99', $hydrate);
        }

        public function getLastHabilitationOrCreate($identifiant, $hydrate = acCouchdbClient::HYDRATE_DOCUMENT){
            $habilitation = $this->getLastHabilitation($identifiant);

            if(!$habilitation) {

                $habilitation = $this->createDoc($identifiant);
            }

            return $habilitation;
        }

        public function getAllEtablissementsWithHabilitations($hydrate = acCouchdbClient::HYDRATE_DOCUMENT){
          $allHabilitations = $this->startkey(self::TYPE_COUCHDB."-")
                      ->endkey(self::TYPE_COUCHDB."-ZZZZZZZZZZZZZZZZZZZZZZZZZZZZ")->execute($hydrate);
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
                if(!$habilitationSuivante) {
                    break;
                }

                $habilitationSuivante->updateHabilitation($hash_produit, $activites, $statut, $commentaire, $date);
                $habilitationSuivante->save();
                $dateCourante = $habilitationSuivante->date;
            }
        }

        public function updateDeclarantAndSave($identifiant, $date, $declarantInfos) {
            $habilitation = $this->createOrGetDocFromIdentifiantAndDate($identifiant, $date);
            foreach(array_keys($habilitation->declarant->toArray(true, false)) as $key) {
                if(isset($declarantInfos[$key])) {
                    $habilitation->declarant->set($key, $declarantInfos[$key]);
                }
            }

            $habilitation->save();

            while($habilitationSuivante = $this->findNextByIdentifiantAndDate($habilitation->identifiant, $habilitation->date)) {
                if(!$habilitationSuivante) {
                    break;
                }

                $habilitationSuivante->declarant->cvi = $declarantInfos['cvi'];
                $habilitationSuivante->save();
                $habilitation = $habilitationSuivante;
            }
        }

        public function getDemande($identifiant, $keyDemande, $date) {
            $habilitation = $this->createOrGetDocFromIdentifiantAndDate($identifiant, $date);

            return $habilitation->demandes->get($keyDemande);
        }

        public function createDemandeAndSave($identifiant, $demandeStatut, $datas, $statut, $date, $commentaire, $auteur) {
            $habilitation = $this->createOrGetDocFromIdentifiantAndDate($identifiant, $date);
            $baseKey = $identifiant."-".str_replace("-", "", $date);
            $demandesKey = array_keys($habilitation->demandes->toArray(true, false));
            ksort($demandesKey);
            $i = 1;
            foreach($demandesKey as $demandeKey) {
                if($demandeKey == sprintf($baseKey."%02d", $i)) {
                    $i++;
                }
            }
            $key = sprintf($baseKey."%02d", $i);
            $demande = $habilitation->demandes->add($key);
            foreach($datas as $key => $value) {
                $demande->donnees->add($key, $value);
            }
            $demande->demande = $demandeStatut;
            $demande->date = $date;
            $demande->statut = $statut;
            $descriptionHistorique = "La demande ".Orthographe::elision("de", strtolower($demande->getDemandeLibelle()))." \"".$demande->getLibelle()."\" a été créée au statut \"".$demande->getStatutLibelle()."\"";


            $historique = $habilitation->addHistorique($descriptionHistorique, $commentaire, $auteur, $statut);
            $historique->iddoc .= ":".$demande->getHash();
            $habilitation->save();

            $this->replicateDemandeAndSave($habilitation, $demande);
            $this->updateAndSaveHabilitationFromDemande($demande, $commentaire);

            return $demande;

        }

        public function updateDemandeAndSave($identifiant, $keyDemande, $date, $statut, $commentaire, $auteur) {
            $demande = $this->getDemande($identifiant, $keyDemande, $date);
            $habilitation = $demande->getDocument();

            $prevStatutLibelle = $demande->statutLibelle;

            $demande->date = $date;
            $demande->statut = $statut;

            $descriptionHistorique = "La demande ".Orthographe::elision("de", strtolower($demande->getDemandeLibelle()))." \"".$demande->getLibelle()."\" est passée au statut \"".$demande->getStatutLibelle()."\"";

            $historique = $habilitation->addHistorique($descriptionHistorique, $commentaire, $auteur, $statut);
            $historique->iddoc .= ":".$demande->getHash();
            $habilitation->save();

            $this->replicateDemandeAndSave($habilitation, $demande);
            $this->updateAndSaveHabilitationFromDemande($demande, $commentaire);

            return $demande;
        }

        protected function replicateDemandeAndSave($habilitation, $demande) {
            while($habilitationSuivante = $this->findNextByIdentifiantAndDate($habilitation->identifiant, $habilitation->date)) {
                if(!$habilitationSuivante) {
                    break;
                }

                if($habilitationSuivante->demandes->exist($demande->getKey()) && $habilitationSuivante->demandes->get($demande->getKey())->date > $demande->date) {
                    break;
                }

                $habilitationSuivante->demandes->add($demande->getKey(), $demande);
                $habilitationSuivante->save();
                $habilitation = $habilitationSuivante;
            }
        }

        protected function updateAndSaveHabilitationFromDemande($demande, $commentaire) {
            if($demande->statut == "COMPLET" && in_array($demande->demande, self::$demandes_produit)) {
                $this->updateAndSaveHabilitation($demande->getDocument()->identifiant, $demande->donnees->produit, $demande->date, $demande->donnees->activites->toArray(true, false), "DEMANDE_HABILITATION", $commentaire);
            }
            if($demande->statut == "VALIDE" && in_array($demande->demande, self::$demandes_produit)) {
                $this->updateAndSaveHabilitation($demande->getDocument()->identifiant, $demande->donnees->produit, $demande->date, $demande->donnees->activites->toArray(true, false), "HABILITE", $commentaire);
            }
            if($demande->statut == "VALIDE" && in_array($demande->demande, self::$demandes_declarant)) {
                $this->updateDeclarantAndSave($demande->getDocument()->identifiant, $demande->date, $demande->donnees->toArray(true, false));
            }
        }
    }
