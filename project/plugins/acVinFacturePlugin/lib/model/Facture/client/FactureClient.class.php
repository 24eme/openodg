<?php
class FactureClient extends acCouchdbClient {

    const TYPE_MODEL = "Facture";
    const TYPE_COUCHDB = "FACTURE";

    const STATUT_REDRESSEE = 'REDRESSE';
    const STATUT_NONREDRESSABLE = 'NON_REDRESSABLE';

    const FACTURE_PAIEMENT_CHEQUE = "CHEQUE";
    const FACTURE_PAIEMENT_VIREMENT = "VIREMENT";
    const FACTURE_PAIEMENT_PRELEVEMENT_AUTO = "PRELEVEMENT_AUTO";
    const FACTURE_PAIEMENT_REMBOURSEMENT = "REMBOURSEMENT";


    const TYPE_DOCUMENT_TOUS = "TOUS";


    public static $origines = array( self::TYPE_DOCUMENT_TOUS => self::TYPE_DOCUMENT_TOUS,
                                     DRevClient::TYPE_MODEL => DRevClient::TYPE_MODEL,
                                    'DR' => 'DR',
                                    'Degustation' => 'Degustation',
                                    'ChgtDenom' => 'ChgtDenom'
                                    );

    public static $types_paiements = array(self::FACTURE_PAIEMENT_CHEQUE => "Chèque", self::FACTURE_PAIEMENT_VIREMENT => "Virement", self::FACTURE_PAIEMENT_PRELEVEMENT_AUTO => "Prélèvement automatique", self::FACTURE_PAIEMENT_REMBOURSEMENT => "Remboursement");

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

    public function createEmptyDoc($compte, $date_facturation = null, $message_communication = null, $region = null, $template = null) {
        $facture = new Facture();
        $facture->storeDatesCampagne($date_facturation);
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
            $region = $compte->region;
        }
        $facture->constructIds($compte);
        $facture->storeEmetteur($region);
        $facture->storeDeclarant($compte);
        $facture->storeTemplates($template);
        if(trim($message_communication)) {
          $facture->addOneMessageCommunication($message_communication);
        }

        return $facture;
    }

    public function createDoc($mouvements, $compte, $date_facturation = null, $message_communication = null, $region = null, $template = null, $arguments = array() ) {
        $facture = $this->createEmptyDoc($compte, $date_facturation, $message_communication, $region, $template);
        $facture->argument = $arguments;
        $facture->storeLignesByMouvements($mouvements, $template);
        $facture->updateTotaux();
        $facture->storeOrigines();
        if(FactureConfiguration::getInstance()->getModaliteDePaiement()) {
            $facture->set('modalite_paiement',FactureConfiguration::getInstance()->getModaliteDePaiement());
        }
        if(trim($message_communication)) {
          $facture->addOneMessageCommunication($message_communication);
        }
        if(FactureConfiguration::getInstance()->hasPaiements()){
          $facture->add("paiements",array());
        }

        if(!$facture->total_ttc && FactureConfiguration::getInstance()->isFacturationAllEtablissements()){
          return null;
        }

        return $facture;
    }

    /** facturation par mvts **/
    public function createDocFromView($mouvements, $compte, $date_facturation = null, $message_communication = null, $region = null, $template = null) {
        if(!$region){
            return null;
        }
        $facture = $this->createEmptyDoc($compte, $date_facturation, $message_communication, $region, $template);

        foreach ($mouvements as $identifiant => $mvt) {
            $facture->storeLignesByMouvementsView($mvt);
        }

        $facture->updateTotaux();

        if(FactureConfiguration::getInstance()->getModaliteDePaiement()) {
            $facture->set('modalite_paiement',FactureConfiguration::getInstance()->getModaliteDePaiement());
        }
        if(FactureConfiguration::getInstance()->hasPaiements()){
          $facture->add("paiements",array());
        }

        if(!$facture->total_ttc && FactureConfiguration::getInstance()->isFacturationAllEtablissements()){
          return null;
        }
        return $facture;
    }

    public function regenerate($facture_or_id) {
        $facture = $facture_or_id;

        if(is_string($facture)) {
            $facture = $this->find($facture_or_id);
        }

        if($facture->isPayee()) {

            throw new sfException(sprintf("La factures %s a déjà été payée", $facture->_id));
        }

        $docs = array();

        foreach($facture->origines as $id) {
            $docs[$id] = $this->getDocumentOrigine($id);
        }

        $mouvements = $this->getMouvementsFacturesByDocs($facture->identifiant, $docs, true);
        $mouvements = $this->aggregateMouvementsFactures($mouvements);

        $template = $facture->getTemplate();
        $message_communication = null;
        if($facture->exist('message_communication')) {
            $message_communication = $facture->message_communication;
        }

        $f = FactureClient::getInstance()->createDoc($mouvements, $facture->getCompte(), date('Y-m-d'), $message_communication, $template->arguments->toArray(true, false), $template);

        $f->_id = $facture->_id;
        $f->_rev = $facture->_rev;
        $f->numero_facture = $facture->numero_facture;
        $f->numero_odg = $facture->numero_odg;
        $f->numero_archive = $facture->numero_archive;

        $f->forceFactureMouvements();

        return $f;
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

                      if(isset($parameters['type_document']) && !in_array($parameters['type_document'], self::$origines)) {
                          unset($mouvements[$key]);
                          $mouvementsBySoc[$identifiant] = $mouvements;
                          continue;
                      }

                      if(isset($parameters['type_document']) && $parameters['type_document'] != "TOUS" && $parameters['type_document'] != $mouvement->key[MouvementFactureView::KEY_TYPE]) {
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
      $modele = ($generation->arguments->exist('modele'))? $generation->arguments->modele : null;

      if(!$modele){
          throw new sfException("La génération ne possède pas de modèle de facture");
      }

      $template = TemplateFactureClient::getInstance()->find($modele);
      if(!$template){
          throw new sfException(sprintf("Le template %s n'existe pas dans la base ", $modele));
      }

      $cpt = 0;

      foreach ($mouvements as $societeID => $mouvementsSoc) {
          $societe = SocieteClient::getInstance()->find($societeID);

          $f = $this->createDocFromView($mouvementsSoc, $societe->getMasterCompte(), $date_facturation, $message_communication, $region, $template);
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
        if(!$arguments['requete'] && FactureConfiguration::getInstance()->isFacturationAllEtablissements()){
          $comptes = CompteAllView::getInstance()->findByInterproVIEW('INTERPRO-declaration');
          foreach($comptes as $compte) {
             $ids[] = $compte->id;
          }
        }else{
          $comptes = CompteClient::getInstance()->getComptes($arguments['requete']);
          foreach($comptes as $compte) {
            $ids[] = $compte->doc['_id'];
          }
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

    public function createAvoir(Facture $f) {
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
      $avoir->reglement_paiement = null;
      $avoir->remove('arguments');
      $avoir->add('arguments');

      return $avoir;
    }

    public function defactureCreateAvoirAndSaveThem(Facture $f) {
      if (!$f->isRedressable()) {
	       return ;
      }
      $avoir = clone $f;
      $compte = CompteClient::getInstance()->find("COMPTE-".$avoir->identifiant);
      $avoir->constructIds($compte, $f->region);
      $f->add('avoir',$avoir->_id);
      $f->save();
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
      $avoir->storeDatesCampagne(date('Y-m-d'));
      $avoir->numero_archive = null;
      $avoir->versement_comptable = 0;
      $avoir->save();
      $f->defacturer();
      $f->save();
      return $avoir;
    }

    public function getFacturesByCompte($identifiant, $hydrate = acCouchdbClient::HYDRATE_DOCUMENT) {
        $ids = $this->startkey(sprintf("FACTURE-%s-%s", $identifiant, "0000000000"))
                    ->endkey(sprintf("FACTURE-%s-%s", $identifiant, "9999999999"))
                    ->execute(acCouchdbClient::HYDRATE_ON_DEMAND)->getIds();

        $factures = array();

        foreach($ids as $id) {
            $factures[$id] = FactureClient::getInstance()->find($id, $hydrate);
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

    public static function generateAuthKey($id)
    {
        return hash('md5', $id . sfConfig::get('app_secret'));
    }
}
