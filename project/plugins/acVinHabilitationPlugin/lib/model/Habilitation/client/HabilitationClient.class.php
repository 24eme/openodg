<?php

class HabilitationClient extends acCouchdbClient {

    const TYPE_MODEL = "Habilitation";
    const TYPE_COUCHDB = "HABILITATION";

    #La liste les activités actives est définie dans habilitation.yml
    const ACTIVITE_PRODUCTEUR = "PRODUCTEUR";
    const ACTIVITE_VINIFICATEUR = "VINIFICATEUR";
    const ACTIVITE_VRAC = "VRAC";
    const ACTIVITE_ELABORATEUR = "ELABORATEUR";
    const ACTIVITE_CONDITIONNEUR = "CONDITIONNEUR";
    const ACTIVITE_VENTE_A_LA_TIREUSE = "VENTE_A_LA_TIREUSE";
    const ACTIVITE_PRODUCTEUR_MOUTS = "PRODUCTEUR_MOUTS";
    const ACTIVITE_ELEVEUR_DGC = "ELEVEUR_DGC";
    const ACTIVITE_NEGOCIANT = "NEGOCIANT";
    const ACTIVITE_MARC = "MARC";
    const ACTIVITE_DISTILLATEUR = "DISTILLATEUR";
    const ACTIVITE_METTEUR_EN_MARCHE = "METTEUR_EN_MARCHE";
    const ACTIVITE_ELEVEUR = 'ELEVEUR';
    const ACTIVITE_PRESTATAIRE_DE_SERVICE = 'PRESTATAIRE_DE_SERVICE';



    const STATUT_DEMANDE_HABILITATION = "DEMANDE_HABILITATION";
    const STATUT_ATTENTE_HABILITATION = "ATTENTE_HABILITATION";
    const STATUT_DEMANDE_RETRAIT = "DEMANDE_RETRAIT";
    const STATUT_DEMANDE_RESILIATION = "DEMANDE_RESILIATION";
    const STATUT_HABILITE = "HABILITE";
    const STATUT_SUSPENDU = "SUSPENDU";
    const STATUT_REFUS = "REFUS";
    const STATUT_RETRAIT = "RETRAIT";
    const STATUT_RESILIE = "RESILIE";
    const STATUT_ANNULE = "ANNULÉ";
    const STATUT_ARCHIVE = "ARCHIVE";

    const DEMANDE_HABILITATION = "HABILITATION";
    const DEMANDE_RETRAIT = "RETRAIT";
    const DEMANDE_RESILIATION = "RESILIATION";

    public static $demande_libelles = array(
        self::DEMANDE_HABILITATION => "Habilitation",
        self::DEMANDE_RETRAIT => "Retrait",
        self::DEMANDE_RESILIATION => "Résiliation",
    );

    public static $demande_droits = array(
        self::DEMANDE_RETRAIT => "INAO",
    );

    public static $statuts_libelles = array( self::STATUT_DEMANDE_HABILITATION => "Demande d'habilitation",
                                             self::STATUT_ATTENTE_HABILITATION => "En attente d'habilitation",
                                             self::STATUT_DEMANDE_RETRAIT => "Demande de retrait",
                                             self::STATUT_DEMANDE_RESILIATION => "Demande de résiliation",
                                             self::STATUT_HABILITE => "Habilité",
                                             self::STATUT_SUSPENDU => "Suspendu",
                                             self::STATUT_REFUS => "Refus",
                                             self::STATUT_ANNULE => "Annulé",
                                             self::STATUT_RETRAIT => "Retrait",
                                             self::STATUT_RESILIE => "Résilié",
                                            self::STATUT_ARCHIVE => "Archivé");

    public static function getInstance()
    {
      return acCouchdbManager::getClient("Habilitation");
    }

    public function getActivites() {

        return HabilitationConfiguration::getInstance()->getActivites();
    }

    public function getDemandes($filtre = null) {
        $demandes = self::$demande_libelles;

        if($filtre) {
            $demandes = array_filter($demandes, function($key) use ($filtre) {
                return isset(self::$demande_droits[$key]) && self::$demande_droits[$key] && preg_match("/".$filtre."/i", self::$demande_droits[$key]);
            }, ARRAY_FILTER_USE_KEY);
        }

        return $demandes;
    }

    public function getDemandeStatuts($filtre = null) {
        $statuts = HabilitationConfiguration::getInstance()->getDemandeStatuts();

        if($filtre) {
            $statuts = array_filter($statuts, function($key) use ($filtre) {
                return preg_match("/".$filtre."/i", $key);
            }, ARRAY_FILTER_USE_KEY);
        }

        return $statuts;
    }

    public function getStatutsFerme() {

        return HabilitationConfiguration::getInstance()->getDemandeStatutsFerme();
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
        $statuts = $this->getDemandeAutomatique();

        if(!isset($statuts[$statut])) {

            return null;
        }

        return $statuts[$statut];
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

        public function isRegionInHabilitation($identifiant, $region) {
            $habilitation = $this->getLastHabilitation($identifiant);
            if(!$habilitation) {

                return false;
            }

            $produits = DRevConfiguration::getInstance()->getOdgProduits($region);
            foreach($produits as $hash) {
                if($habilitation->containHashProduit($hash)) {
                    return true;
                }
            }

            return false;
        }

        public function getAllEtablissementsWithHabilitations($hydrate = acCouchdbClient::HYDRATE_JSON){
          $allHabilitations = $this->startkey(self::TYPE_COUCHDB."-")
                      ->endkey(self::TYPE_COUCHDB."-ZZZZZZZZZZZZZZZZZZZZZZZZZZZZ")->execute($hydrate);
          $etbIds = array();
          foreach ($allHabilitations as $habilitation) {
            if (!isset($etbIds[$habilitation->identifiant]) || $etbIds[$habilitation->identifiant] < $habilitation->_id) {
                $etbIds[$habilitation->identifiant] = $habilitation->_id;
            }
          }
          krsort($etbIds);
          return $etbIds;
        }

        public function updateAndSaveHabilitation($etablissementIdentifiant, $hash_produit, $date, $activites, $statut, $commentaire = "") {
            $last = $this->getLastHabilitation($etablissementIdentifiant);
            $habilitation = $this->findPreviousByIdentifiantAndDate($etablissementIdentifiant, $date);
            if($habilitation && $last && $habilitation->_id < $last->_id) {
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

        public function getLastDemande($identifiant, $keyDemande) {
            $habilitation = $this->getLastHabilitation($identifiant);

            return $habilitation->demandes->get($keyDemande);
        }

        public function createDemandeAndSave($identifiant, $demandeStatut, $produitHash, $activites, $statut, $date, $commentaire, $auteur, $trigger = true) {
            $habilitation = $this->createOrGetDocFromIdentifiantAndDate($identifiant, $date);
            $baseKey = $identifiant."-".str_replace("-", "", $date);
            $demandesKey = array_keys($habilitation->demandes->toArray(true, false));
            ksort($demandesKey);
            $biggerNum = 0;
            foreach($demandesKey as $demandeKey) {
                if(!preg_match('/^'.$baseKey.'([0-9]+)$/', $demandeKey, $matches)) {
                    continue;
                }

                if((int) $matches[1] > $biggerNum) {
                    $biggerNum = (int) $matches[1];
                }
            }
            $key = sprintf($baseKey."%02d", $biggerNum + 1);
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

        public function updateDemandeAndSave($identifiant, $keyDemande, $date, $statut, $commentaire, $auteur, $trigger = true) {
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

        public function deleteDemandeAndSave($identifiant, $keyDemande) {
            $habilitation = $this->getLastHabilitation($identifiant);
            while($habilitation) {
                if(!$habilitation->demandes->exist($keyDemande)) {
                    break;
                }
                $newHistorique = array();
                foreach($habilitation->historique as $h) {
                    if(preg_match("/".$keyDemande."/", $h->iddoc)) {
                        continue;
                    }

                    $newHistorique[] = $h;
                }
                $habilitation->remove('historique');
                $habilitation->add('historique', $newHistorique);
                $habilitation->demandes->remove($keyDemande);
                $habilitation->save();

                $date = new DateTime($habilitation->date);
                $date = $date->modify('-1 day');

                $habilitation = $this->findPreviousByIdentifiantAndDate($habilitation->identifiant, $date->format('Y-m-d'));
            }
        }

        public function deleteDemandeLastStatutAndSave($identifiant, $keyDemande) {
            $habilitation = $this->getLastHabilitation($identifiant);

            $demande = $this->getDemande($identifiant, $keyDemande, $habilitation->demandes->get($keyDemande)->date);

            $historiquePrec = $demande->getHistoriquePrecedent($demande->statut, $demande->date);

            if(!$historiquePrec) {

                return;
            }

            $keyHistoriqueToDelete = null;
            foreach($demande->getDocument()->historique as $h) {
                if($h->iddoc != $demande->getDocument()->_id.":".$demande->getHash()) {
                    continue;
                }
                if($h->statut != $demande->statut)  {
                    continue;
                }

                $keyHistoriqueToDelete = $h->getKey();
                break;
            }

            if($keyHistoriqueToDelete !== null) {
                $demande->getDocument()->historique->remove($keyHistoriqueToDelete);
            }

            $demande->statut = $historiquePrec->statut;
            $demande->date = $historiquePrec->date;
            $demande->commentaire = $historiquePrec->commentaire;
            $demande->getDocument()->save();

            if($historiquePrec->iddoc != $demande->getDocument()->_id.":".$demande->getHash()) {
                $demande = $this->getDemande($identifiant, $keyDemande, $historiquePrec->date);
            }

            $this->replicateDemandeAndSave($demande, true);
        }

        public function splitDemandeAndSave($identifiant, $keyDemande, $activites) {
            $demande = $this->getLastDemande($identifiant, $keyDemande);
            $activitesToKeep = array();
            foreach($demande->activites as $activite) {
                if(in_array($activite, $activites)) {
                    continue;
                }
                $activitesToKeep[] = $activite;
            }
            if(!count($activitesToKeep)) {
                return null;
            }

            $newKeyDemande1 = null;
            $newKeyDemande2 = null;
            foreach($demande->getFullHistorique() as $historique) {
                if(!$newKeyDemande1) {
                    $newKeyDemande1 = $this->createDemandeAndSave($identifiant, $demande->demande, $demande->produit, $activites, $historique->statut, $historique->date, $historique->commentaire, $historique->auteur, false)->getKey();
                    $newKeyDemande2 = $this->createDemandeAndSave($identifiant, $demande->demande, $demande->produit, $activitesToKeep, $historique->statut, $historique->date, $historique->commentaire, $historique->auteur, false)->getKey();
                    continue;
                }

                $this->updateDemandeAndSave($identifiant, $newKeyDemande1, $historique->date, $historique->statut, $historique->commentaire, $historique->auteur, false);
                $this->updateDemandeAndSave($identifiant, $newKeyDemande2, $historique->date, $historique->statut, $historique->commentaire, $historique->auteur, false);
            }

            $this->deleteDemandeAndSave($identifiant, $keyDemande);

            return array($this->getLastDemande($identifiant, $newKeyDemande1), $this->getLastDemande($identifiant, $newKeyDemande2));
        }

        protected function postSaveDemande($demande, $commentaire, $auteur, $trigger) {
            $this->replicateDemandeAndSave($demande);

            if($trigger) {
                $this->updateAndSaveHabilitationFromDemande($demande, $commentaire);
                $this->triggerDemandeStatutAndSave($demande, $commentaire, $auteur);
            }
        }

        protected function replicateDemandeAndSave($demande, $force = false) {
            $habilitation = $demande->getDocument();
            while($habilitationSuivante = $this->findNextByIdentifiantAndDate($habilitation->identifiant, $habilitation->date)) {
                if(!$habilitationSuivante) {
                    break;
                }

                if($habilitationSuivante->demandes->exist($demande->getKey()) && $habilitationSuivante->demandes->get($demande->getKey())->date > $demande->date && !$force) {
                    break;
                }

                $habilitationSuivante->demandes->add($demande->getKey(), $demande);
                $habilitationSuivante->save();
                $habilitation = $habilitationSuivante;
            }
        }

        public function triggerDemandeStatutAndSave($demande, $commentaire, $auteur, $date = null) {
            if(!array_key_exists($demande->statut, $this->getDemandeAutomatique())) {
                return;
            }

            $statutAutomatique = $this->getDemandeAutomatiqueStatut($demande->statut);

            if(!$date && $this->getDemandeHabilitationsByTypeDemandeAndStatut($demande->demande, $statutAutomatique)) {
                $date = $demande->date;
            }

            if(!$date) {
                $date = date('Y-m-d');
            }

            $demande = $this->updateDemandeAndSave($demande->getDocument()->identifiant, $demande->getKey(), $date, $statutAutomatique, $commentaire, $auteur);

            return $demande;
        }

        public function updateAndSaveHabilitationFromDemande($demande, $commentaire) {
            $statutHabilitation = $this->getDemandeHabilitationsByTypeDemandeAndStatut($demande->demande, $demande->statut);
            if(!$statutHabilitation) {

                return;
            }

            $this->updateAndSaveHabilitation($demande->getDocument()->identifiant, $demande->produit, $demande->date, $demande->activites->toArray(true, false), $statutHabilitation, $commentaire);
        }

        public function getProduitsConfig($config) {
          $produits = array();
          foreach ($config->declaration->getProduits() as $produit) {
              $produithab = HabilitationConfiguration::getInstance()->getProduitAtHabilitationLevel($produit);
              $produits[$produithab->getHash()] = $produithab;
          }
          return $produits;
        }



    }
