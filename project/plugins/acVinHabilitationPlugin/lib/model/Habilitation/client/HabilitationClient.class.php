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
    const STATUT_ARCHIVE = "ARCHIVE";

    const DEMANDE_HABILITATION = "HABILITATION";
    const DEMANDE_RETRAIT = "RETRAIT";

    public static $demande_libelles = array(
        self::DEMANDE_HABILITATION => "Habilitation",
        self::DEMANDE_RETRAIT => "Retrait"
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

    public function getActivites() {

        return HabilitationConfiguration::getInstance()->getActivites();
    }

    public function getDemandeStatuts() {

        return HabilitationConfiguration::getInstance()->getDemandeStatuts();
    }

    public function getDemandeStatutLibelle($key) {
        $statuts = HabilitationConfiguration::getInstance()->getDemandeStatuts();

        if(!isset($statuts[$key])) {

            return null;
        }

        return $statuts[$key];
    }

    public function getDemandeAutomatique() {

        return HabilitationConfiguration::getInstance()->getDemandeAutomatique();
    }

    public function getDemandeAutomatiqueStatut($statut) {

        return $this->getDemandeAutomatique()[$statut];
    }

    public function getDemandeHabilitations() {

        return HabilitationConfiguration::getInstance()->getDemandeHabilitations();
    }

    public function getDemandeHabilitationsByTypeDemandeAndStatut($typeDemande, $statut) {
        $habilitations = $this->getDemandeHabilitations();

        if(!isset($habilitations[$typeDemande][$statut])) {
            return null;
        }

        return $habilitations[$typeDemande][$statut];
    }

    public function getLibelleActivite($key) {

        if(!isset($this->getActivites()[$key])) {

            return $key;
        }

        return $this->getActivites()[$key];
    }

    public function getLibelleStatut($key) {

        if(isset(self::$statuts_libelles[$key])) {

            return self::$statuts_libelles[$key];
        }

        if($this->getDemandeStatutLibelle($key)) {

            return $this->getDemandeStatutLibelle($key);
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

        public function getDemande($identifiant, $keyDemande, $date) {
            $habilitation = $this->createOrGetDocFromIdentifiantAndDate($identifiant, $date);

            return $habilitation->demandes->get($keyDemande);
        }

        public function createDemandeAndSave($identifiant, $demandeStatut, $produitHash, $activites, $statut, $date, $commentaire, $auteur, $trigger = false) {
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

            $demande->produit = $produitHash;
            $demande->activites = $activites;
            $demande->demande = $demandeStatut;

            $demande->commentaire = $commentaire;
            $demande->getLibelle();

            $this->updateDemandeStatut($demande, $date, $statut, $commentaire, $auteur, true);

            $habilitation->save();

            $this->postSaveDemande($demande, $commentaire, $auteur, $trigger);

            return $demande;
        }

        public function updateDemandeAndSave($identifiant, $keyDemande, $date, $statut, $commentaire, $auteur, $trigger = false) {
            $demande = $this->getDemande($identifiant, $keyDemande, $date);
            $habilitation = $demande->getDocument();

            $this->updateDemandeStatut($demande, $date, $statut, $commentaire, $auteur);

            $habilitation->save();

            $this->postSaveDemande($demande, $commentaire, $auteur, $trigger);

            return $demande;
        }

        protected function updateDemandeStatut($demande, $date, $statut, $commentaire, $auteur, $creation = false) {
            $habilitation = $demande->getDocument();
            $demande->date = $date;
            $demande->statut = $statut;

            if(!$demande->date_habilitation) {
                $demande->date_habilitation = $demande->date;
            }

            if($this->getDemandeHabilitationsByTypeDemandeAndStatut($demande->demande, $demande->statut)) {
                $demande->date_habilitation = $demande->date;
            }

            $descriptionHistorique = "La demande ".Orthographe::elision("de", strtolower($demande->getDemandeLibelle()))." \"".$demande->getLibelle()."\" ".(($creation) ? "a été créée" : "est passée")." au statut \"".$demande->getStatutLibelle()."\"";

            $historique = $habilitation->addHistorique($descriptionHistorique, $commentaire, $auteur, $statut);
            $historique->iddoc .= ":".$demande->getHash();
        }

        protected function postSaveDemande($demande, $commentaire, $auteur, $trigger) {
            $this->replicateDemandeAndSave($demande);
            $this->updateAndSaveHabilitationFromDemande($demande, $commentaire);

            if($trigger) {
                $this->triggerDemandeStatutAndSave($demande, date('Y-m-d'), $commentaire, $auteur);
            }
        }

        protected function replicateDemandeAndSave($demande) {
            $habilitation = $demande->getDocument();
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

        public function triggerDemandeStatutAndSave($demande, $date, $commentaire, $auteur) {
            if(!array_key_exists($demande->statut, $this->getDemandeAutomatique())) {
                return;
            }

            $demande = $this->updateDemandeAndSave($demande->getDocument()->identifiant, $demande->getKey(), $date, $this->getDemandeAutomatiqueStatut($demande->statut), $commentaire, $auteur);

            return $demande;
        }

        protected function updateAndSaveHabilitationFromDemande($demande, $commentaire) {
            $statutHabilitation = $this->getDemandeHabilitationsByTypeDemandeAndStatut($demande->demande, $demande->statut);
            if(!$statutHabilitation) {

                return;
            }

            $this->updateAndSaveHabilitation($demande->getDocument()->identifiant, $demande->produit, $demande->date, $demande->activites->toArray(true, false), $statutHabilitation, $commentaire);
        }
    }
