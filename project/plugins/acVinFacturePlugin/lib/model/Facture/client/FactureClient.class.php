<?php
class FactureClient extends acCouchdbClient {

    const TYPE_MODEL = "Facture";
    const TYPE_COUCHDB = "FACTURE";

    const STATUT_REDRESSEE = 'REDRESSE';
    const STATUT_NONREDRESSABLE = 'NON_REDRESSABLE';

    const FACTURE_PAIEMENT_CHEQUE = "CHEQUE";
    const FACTURE_PAIEMENT_VIREMENT = "VIREMENT";
    const FACTURE_PAIEMENT_PRELEVEMENT_AUTO = "PRELEVEMENT_AUTO";
    const FACTURE_REJET_PRELEVEMENT = "REJET_PRELEVEMENT";
    const FACTURE_PAIEMENT_REMBOURSEMENT = "REMBOURSEMENT";
    const FACTURE_PAIEMENT_ESPECES = "ESPECES";
    const FACTURE_PAIEMENT_CARTE_BANCAIRE = "CARTE_BANCAIRE";

    public static $types_paiements = array(self::FACTURE_PAIEMENT_CHEQUE => "Chèque", self::FACTURE_PAIEMENT_VIREMENT => "Virement", self::FACTURE_PAIEMENT_PRELEVEMENT_AUTO => "Prélèvement automatique", self::FACTURE_PAIEMENT_PRELEVEMENT_AUTO => "Prélèvement automatique", self::FACTURE_REJET_PRELEVEMENT => "Rejet de prélèvement", self::FACTURE_PAIEMENT_REMBOURSEMENT => "Remboursement", self::FACTURE_PAIEMENT_ESPECES => "Espèces", self::FACTURE_PAIEMENT_CARTE_BANCAIRE => "Carte Bancaire");

    private $documents_origine = array();

    public static function getInstance() {
        return acCouchdbManager::getClient("Facture");
    }

    public function getId($identifiant, $numeroFacture) {
        return 'FACTURE-'.$identifiant.'-'.$numeroFacture;
    }

    public function getNextNoFacture($idClient,$date)
    {
      $id = '';
    	$facture = self::getAtDate($idClient,$date, acCouchdbClient::HYDRATE_ON_DEMAND)->getIds();
        if (count($facture) > 0) {
            $id .= ((double)str_replace('FACTURE-'.$idClient.'-', '', max($facture)) + 1);
        } else {
            $id.= $date.'01';
        }
      return $id;
    }

    // Fonction obsolète mais encore utilisée pour Nantes
    public function getNextNoFactureCampagneFormatted($idClient, $campagne, $format, $document_origine = null){
        $annee = DateTime::createFromFormat("Y",$campagne)->format("y");
        $archiveNumero = ArchivageAllView::getInstance()->getLastNumeroArchiveByTypeAndCampagne("Facture", $campagne);
        if($document_origine){
            return sprintf($format, $annee, $document_origine, intval($archiveNumero) + 1);
        }
        return sprintf($format, $annee, intval($archiveNumero) + 1);
    }

    public function getAtDate($idClient,$date, $hydrate = acCouchdbClient::HYDRATE_DOCUMENT) {
        return $this->startkey('FACTURE-'.$idClient.'-'.$date.'00')->endkey('FACTURE-'.$idClient.'-'.$date.'99')->execute($hydrate);
    }

    public function getMouvementsFacturesByDocs($compteIdentifiant, $docs, $regenerate = false) {
        if (!$docs) {

            return array();
        }
        $mouvements = array();
        if($docs && !is_array($docs)){
          $docs = array($docs);
        }
        foreach($docs as $id => $doc) {
            if($regenerate) {
                $doc->remove('mouvements');
                $doc->add('mouvements');
            }

            $generated = false;
            if(!count($doc->mouvements)) {
                $doc->generateMouvementsFactures();
                $doc->save();
                $generated = true;
            }

            if($generated && count($doc->mouvements) && !$doc->exist('mouvements/'.$compteIdentifiant)) {
                $mouvs = $doc->mouvements->getFirst();
                $doc->mouvements->add($compteIdentifiant, $mouvs->toArray(true, false));
                $doc->mouvements->remove($mouvs->getKey());
            }

            if($generated) {
                $doc->save();
            }

            if(!$doc->exist('mouvements/'.$compteIdentifiant)) {

                continue;
            }

            $mouvs = $doc->mouvements->get($compteIdentifiant);

            foreach($mouvs as $m) {
                if((!$m->isFacturable() || $m->facture)) {
                    continue;
                }
                $mouvements[] = $m;
            }
        }

        return $mouvements;
    }

    public function aggregateMouvementsFactures($mouvements) {
        $mouvementsAggreges = array();

        foreach($mouvements as $mouv) {
          $key = $mouv->categorie.$mouv->type_hash.$mouv->taux;
            if($mouv->exist("template")){
              $key = $mouv->template.$key;
            }

            if(!isset($mouvementsAggreges[$key])) {
                $mouvementsAggreges[$key] = array(
                    "categorie" => $mouv->categorie,
                    "type_hash" => $mouv->type_hash,
                    "type_libelle" => $mouv->type_libelle,
                    "quantite" => 0,
                    "taux" => $mouv->taux,
                    "origines" => array(),
                );
                if($mouv->exist("tva")){
                    $mouvementsAggreges[$key]["tva"] = $mouv->tva;
                }
                if($mouv->exist("unite")){
                  $mouvementsAggreges[$key]["unite"] = $mouv->unite;
                }
            }

            $mouvementsAggreges[$key]["quantite"] += $mouv->quantite;
            if(!isset($mouvementsAggreges[$key]["origines"][$mouv->getDocument()->_id])) {
                $mouvementsAggreges[$key]["origines"][$mouv->getDocument()->_id] = array();
            }
            $mouvementsAggreges[$key]["origines"][$mouv->getDocument()->_id][] = $mouv->getKey();
        }

        return array_values($mouvementsAggreges);
    }

    public function createEmptyDoc($compte, $date_facturation = null, $message_communication = null, $region = null, $date_emission = null) {
        $facture = new Facture();
        $facture->storeDatesCampagne($date_facturation, $date_emission);
        if(get_class($compte) == "stdClass") {
            if(isset($compte->compte)) {
                $id = $compte->compte;
            }else{
                $id = $compte->_id;
            }
            $compte = CompteClient::getInstance()->find($id);
        }
        // Attention le compte utilisé pour OpenOdg est celui de la société
        if($compte->exist('id_societe')){
          $compte = $compte->getSociete()->getMasterCompte();
        }
        if (!$region) {
            $region = Organisme::getCurrentRegion();
        }
        $facture->identifiant = method_exists($compte, 'getSociete') ? $compte->getSociete()->identifiant : $compte->identifiant;
        $facture->region = $region;
        $facture->constructIds();
        $facture->storeEmetteur();
        $facture->storeDeclarant($compte);
        if(trim($message_communication)) {
          $facture->addOneMessageCommunication($message_communication);
        }

        return $facture;
    }

    public function createDocFromView($mouvements, $compte, $date_facturation = null, $message_communication = null, $region = null) {
        if(!$region){
            $region = Organisme::getCurrentRegion();
        }

        $facture = $this->createEmptyDoc($compte, $date_facturation, $message_communication, $region);
        $types = [];
        $templates = [];

        foreach($mouvements as $mvt) {
            $types[$mvt->value->type] = $mvt->value->type;
            $campagne = substr($mvt->value->campagne, 0, 4);

            if (in_array($campagne, $templates)) {
                continue;
            }

            $templates[] = TemplateFactureClient::getInstance()->getTemplateIdFromCampagne($campagne, strtoupper(sfConfig::get('app_region', sfConfig::get('sf_app'))));
        }
        $templates = array_unique($templates);

        $facture->storeTemplates($templates);

        foreach ($templates as $template) {
            foreach($template->cotisations as $configCollection) {
                if(!$configCollection->isForType($types)) {
                    continue;
                }
                if(!$configCollection->isRequired()) {
                    continue;
                }
                $facture->addLigne($configCollection)->updateTotaux();
            }
        }

        $lignes = array();
        $lignes_originaux = array();
        foreach ($mouvements as $identifiant => $mvt) {
            $cle = $mvt->value->categorie.$mvt->value->detail_libelle.$mvt->value->type_libelle.$mvt->value->taux.$mvt->value->tva;
            if (isset($mvt->value->unite)) {
                $cle .= $mvt->value->unite;
            }
            $lignes_originaux[$cle][] = $mvt;
            if (isset($lignes[$cle])) {
                $lignes[$cle]->value->quantite += $mvt->value->quantite;
            }else{
                $lignes[$cle] = $mvt;
            }
        }
        foreach($lignes as $cle => $ligne) {
            $facture->storeLignesByMouvementsView($ligne, $lignes_originaux[$cle]);
        }

        $facture->orderLignesByCotisationsKeys();
        $facture->updateTotaux();

        if(class_exists("Societe") && $facture->getSociete()->hasMandatSepaActif()){    // si il a un mandat sepa j'ajoute directement le noeud
            $facture->addPrelevementAutomatique();
        }

        if(FactureConfiguration::getInstance()->getModaliteDePaiement()) {
            $facture->set('modalite_paiement',FactureConfiguration::getInstance()->getModaliteDePaiement());
        }
        if(FactureConfiguration::getInstance()->hasPaiements()){
          $facture->add("paiements",array());
        }
        if(!$facture->total_ttc){
          return null;
        }
        return $facture;
    }

    public function getDocumentOrigine($id) {
        if (!array_key_exists($id, $this->documents_origine)) {
            $this->documents_origine[$id] = acCouchdbManager::getClient()->find($id);
        }
        return $this->documents_origine[$id];
    }

    public function findByIdentifiant($identifiant) {
        return $this->find('FACTURE-' . $identifiant);
    }

    public function findBySocieteAndId($idSociete, $idFacture) {
        return $this->find('FACTURE-'.$idSociete . '-' . $idFacture);
    }

    public function getFacturationForSociete($societe) {
      return MouvementFactureView::getInstance()->getMouvementsFacturesBySociete($societe, 0, 1);
    }


    public function getMouvementsFacturesNonFacturesBySoc($mouvements) {
        $generationFactures = array();
        foreach ($mouvements as $mouvement) {
	  $societe_id = substr($mouvement->key[MouvementFactureView::KEYS_ETB_ID], 0, -2);
	  if (isset($generationFactures[$societe_id])) {
	    $generationFactures[$societe_id][] = $mouvement;
	  } else {
	    $generationFactures[$societe_id] = array();
	    $generationFactures[$societe_id][] = $mouvement;
	  }
        }
        return $generationFactures;
    }

    public function filterWithParameters($mouvementsBySoc, $parameters) {
        if (isset($parameters['date_mouvement']) && ($parameters['date_mouvement'])){
          $date_mouvement = Date::getIsoDateFromFrenchDate($parameters['date_mouvement']);
          foreach ($mouvementsBySoc as $identifiant => $mouvements) {
              foreach ($mouvements as $key => $mouvement) {
                      $farDateMvt = $this->getGreatestDate(preg_replace("/([0-9]{4}-[0-9]{2}-[0-9]{2})(.+)/","$1",$mouvement->key[MouvementFactureView::KEY_DATE]));
                      if(Date::sup($farDateMvt,$date_mouvement)) {
  		                    unset($mouvements[$key]);
                          $mouvementsBySoc[$identifiant] = $mouvements;
                          continue;
                      }

                      if(isset($parameters['type_document']) && !in_array($parameters['type_document'], FactureConfiguration::getInstance()->getTypesDocumentFacturant())) {
                          unset($mouvements[$key]);
                          $mouvementsBySoc[$identifiant] = $mouvements;
                          continue;
                      }

                      if(isset($parameters['type_document']) && $parameters['type_document'] != "TOUS" && $parameters['type_document'] != $mouvement->key[MouvementFactureView::KEY_TYPE]) {
                        unset($mouvements[$key]);
                        $mouvementsBySoc[$identifiant] = $mouvements;
                        continue;
                      }

                      if(isset($parameters['region']) && $parameters['region'] && isset($mouvement->value->region) && $parameters['region'] != $mouvement->value->region) {
                        unset($mouvements[$key]);
                        $mouvementsBySoc[$identifiant] = $mouvements;
                        continue;
                      }
              }
          }
      }
      $mouvementsBySoc = $this->cleanMouvementsBySoc($mouvementsBySoc);
      return $mouvementsBySoc;
    }

    public function createFacturesBySoc($mouvements, $date_facturation, $message_communication = null, $generation = null) {
      if(!$generation){
          $generation = new Generation();
          $generation->type_document = GenerationClient::TYPE_DOCUMENT_FACTURES;
      }

      $generation->documents = array();
      $generation->somme = 0;
      $region = ($generation->arguments->exist('region'))? $generation->arguments->region : null;

      $cpt = 0;

      foreach ($mouvements as $societeID => $mouvementsSoc) {
          $compte = null;
          if(class_exists("Societe")) {
              if ($societe = SocieteClient::getInstance()->find($societeID)) {
                  $compte = $societe->getMasterCompte();
              }
          }
          if (!$compte) {
              $compte = CompteClient::getInstance()->findByIdentifiant($societeID);
          }
          if (!$compte) {
              continue;
          }

          $f = $this->createDocFromView($mouvementsSoc, $compte, $date_facturation, $message_communication, $region);
          if(!$f) {
               continue;
          }
          $f->save();
          $generation->somme += $f->total_ttc;
          $generation->add('documents')->add($cpt, $f->_id);
          $cpt++;
      }

      return $generation;
  }

    public function getComptesIdFilterWithParameters($arguments) {
        $ids = array();
        if($arguments['compte']){

            //TODO: il faudra gérer le multi etb
            $ids[] = $arguments['compte'];
            return $ids;
        }

        if ($argument['requete']) {  //Pour l'AVA déprécié
          $comptes = CompteClient::getInstance()->getComptes($arguments['requete']);
          foreach($comptes as $compte) {
            $ids[] = $compte->doc['_id'];
          }

          return $ids;
        }

        if(!class_exists("CompteAllView")) { //Pour l'AVA

            return CompteClient::getInstance()->getAll(acCouchdbClient::HYDRATE_ON_DEMAND)->getIds();
        }


        $comptes = CompteAllView::getInstance()->findByInterproVIEW('INTERPRO-declaration');
        foreach($comptes as $compte) {
            $ids[] = $compte->id;
        }


        return $ids;
    }

    private function getGreatestDate($dates){
        if(is_string($dates)) return $dates;
        if(is_array($dates)){
            $dateres = $dates[0];
            foreach ($dates as $date) {
                if(Date::sup($date, $dateres)) $dateres=$date;
            }
            return $dateres;
        }
         throw new sfException("La date du mouvement ou le tableau de date est mal formé ".print_r($dates, true));
    }

    private function cleanMouvementsBySoc($mouvementsBySoc){
      if (count($mouvementsBySoc) == 0)
	return null;
      foreach ($mouvementsBySoc as $identifiant => $mouvement) {
	if (!count($mouvement))
	  unset($mouvementsBySoc[$identifiant]);
      }
      return $mouvementsBySoc;
    }


    public function getProduitsFromTypeLignes($lignes) {
        $produits = array();
        foreach ($lignes as $ligne) {
            if (array_key_exists($ligne->produit_hash, $produits)) {
                $produits[$ligne->produit_hash][] = $ligne;
            } else {
                $produits[$ligne->produit_hash] = array();
                $produits[$ligne->produit_hash][] = $ligne;
            }
        }
        return $produits;
    }

    public function isRedressee($factureview){
      return ($factureview->value[FactureSocieteView::VALUE_STATUT] == self::STATUT_REDRESSEE);
    }

    public function isRedressable($factureview){
      return !$this->isRedressee($factureview) && $factureview->value[FactureSocieteView::VALUE_STATUT] != self::STATUT_NONREDRESSABLE;
    }

    public function createAvaAvoir(Facture $f) {
      if (!$f->isRedressable()) {
  return ;
      }
      $avoir = clone $f;

      $avoir->constructIds($f->getCompte(), $f->region);

      foreach($avoir->lignes as $ligne) {
          foreach($ligne->details as $detail) {
              $detail->quantite *= -1;
              $detail->montant_ht *= -1;
              $detail->montant_tva *= -1;
          }

          $ligne->montant_ht *= -1;
          $ligne->montant_tva *= -1;

          $ligne->remove('origine_mouvements');
          $ligne->add('origine_mouvements');
      }

      $avoir->total_ht *= -1;
      $avoir->total_taxe *= -1;
      $avoir->total_ttc *= -1;

      $avoir->remove('origines');
      $avoir->add('origines');

      $avoir->remove('templates');
      $avoir->add('templates');

      $avoir->numero_archive = null;
      $avoir->numero_odg = null;
      $avoir->versement_comptable = 0;
      $avoir->versement_comptable_paiement = 1;
      $avoir->storeDatesCampagne(date('Y-m-d'));
      $avoir->date_paiement = null;
      $avoir->remove('arguments');
      $avoir->add('arguments');

      $avoir->date_paiement = null;
      $avoir->modalite_paiement = null;
      $avoir->remove('reglement_paiement');
      $avoir->remove('paiements');
      $avoir->add('paiements');
      $avoir->remove('montant_paiement');
      $avoir->add('montant_paiement');

      return $avoir;
    }

    public function defactureCreateAvoirAndSaveThem(Facture $f, $date = null) {
      if (!$f->isRedressable()) {
	       return ;
      }
      if (!$date) {
          $date = date('Y-m-d');
      }
      $avoir = clone $f;
      $compte = CompteClient::getInstance()->find("COMPTE-".$avoir->identifiant);
      $avoir->constructIds($compte, $f->region);
      $f->add('avoir',$avoir->_id);
      $paiements = [];
      foreach($f->paiements as $p) {
        if( ($p->type_reglement != FactureClient::FACTURE_PAIEMENT_PRELEVEMENT_AUTO) || $p->execute) {
              $paiements[] = $p;
        }

        $f->remove('paiements');
        $f->add('paiements');
        $f->paiements = $paiements;
        $f->updateMontantPaiement();
      }
      foreach($avoir->lignes as $type => $ligne) {
        $ligne->montant_ht *= -1;
        $ligne->montant_tva *= -1;
      	foreach($ligne->details as $id => $detail) {
          $detail->quantite *= -1;
      	  $detail->montant_ht *= -1;
          $detail->montant_tva *= -1;
      	}
      }
      $avoir->total_ttc *= -1;
      $avoir->total_ht *= -1;
      $avoir->total_taxe *= -1;
      $avoir->remove('echeances');
      $avoir->add('echeances');
      $avoir->statut = self::STATUT_NONREDRESSABLE;
      $avoir->storeDatesCampagne($date);
      $avoir->numero_archive = null;
      $avoir->numero_odg = null;
      $avoir->versement_comptable = 0;
      $avoir->versement_comptable_paiement = 0;
      $avoir->remove('date_telechargement');
      $avoir->remove('paiements');
      $avoir->add('paiements');
      $avoir->montant_paiement = null;
      $avoir->remove('pieces');
      $avoir->date_paiement = null;
      $avoir->modalite_paiement = null;
      $avoir->versement_sepa = 1;
      $avoir->save();
      $f->defacturer();
      $f->save();
      return $avoir;
    }

    public function getFacturesByCompte($identifiant, $hydrate = acCouchdbClient::HYDRATE_DOCUMENT, $campagne = null, $limit = null, $region = null) {
        $this->startkey(sprintf("FACTURE-%s-%s", $identifiant, "9999999999"))
             ->endkey(sprintf("FACTURE-%s-%s", $identifiant, "0000000000"))
             ->descending(true);

        if($limit) {
            $this->limit($limit);
        }

        $ids = $this->execute(acCouchdbClient::HYDRATE_ON_DEMAND)->getIds();

        $factures = array();

        foreach($ids as $id) {
            $f = FactureClient::getInstance()->find($id, $hydrate);

            if ($region && $f->region !== $region) {
                continue;
            }

            if (! $campagne) {
                $factures[$id] = $f;
                continue;
            }

            if ($f->campagne == $campagne) {
                $factures[$id] = $f;
            }
        }

        krsort($factures);

        return $factures;
    }

    public function getDateCreation($id) {
        $d = substr($id, -10,8);
        $matches = array();
        if(preg_match('/^([0-9]{4})([0-9]{2})([0-9]{2})$/', $d, $matches)){
        return $matches[3].'/'.$matches[2].'/'.$matches[1];
        }
        return '';
    }

    public function getCampagneByDate($dateFacturation) {
        $dateCampagne = new DateTime($dateFacturation);

        if (FactureConfiguration::getInstance()->getExercice() == 'viticole') {
            $dateCampagne = $dateCampagne->modify('-7 months');
        }elseif (FactureConfiguration::getInstance()->getExercice() == 'recolte') {
            $dateCampagne = $dateCampagne->modify('-9 months');
        }elseif (preg_match('/\d*\/(\d{2})/', FactureConfiguration::getInstance()->getExercice(), $m)) {
            $dateCampagne = $dateCampagne->modify('-'.($m[1] * 1).' months');
        }

        return $dateCampagne->format('Y');
    }

    public function getLastFactures($campagne) {
        $factures = acCouchdbManager::getClient()
            ->startkey(array("Facture", $campagne, array()))
            ->endkey(array("Facture", $campagne))
            ->reduce(false)
            ->include_docs(true)
            ->descending(true)
            ->getView('declaration', 'export')->rows;

        if (FactureConfiguration::getInstance()->hasFacturationParRegion() && RegionConfiguration::getInstance()->hasOdgProduits()) {
            $region = Organisme::getInstance()->getCurrentRegion();
            $factures = array_filter($factures, function ($facture) use ($region) {
                return $facture->doc->region === $region;
            });
        }

        usort($factures, function($a, $b) {
            return strtotime($b->doc->date_facturation) - strtotime($a->doc->date_facturation);
        });
        return array_slice($factures, 0, 10);
    }

    public function getAllFactures($campagne) {
        $factures = acCouchdbManager::getClient()
            ->startkey(array("Facture", $campagne, array()))
            ->endkey(array("Facture", $campagne))
            ->reduce(false)
            ->include_docs(true)
            ->descending(true)
            ->getView('declaration', 'export')->rows;

        if (FactureConfiguration::getInstance()->hasFacturationParRegion() && RegionConfiguration::getInstance()->hasOdgProduits()) {
            $region = Organisme::getInstance()->getCurrentRegion();
            $factures = array_filter($factures, function ($facture) use ($region) {
                return $facture->doc->region === $region;
            });
        }

        usort($factures, function($a, $b) {
            return strtotime($b->doc->date_facturation) - strtotime($a->doc->date_facturation);
        });
        return $factures;
    }


}
