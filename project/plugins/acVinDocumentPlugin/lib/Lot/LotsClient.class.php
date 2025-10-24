<?php

class LotsClient
{
    protected static $self = null;
    protected $mouvements_declarant = [];

    const INITIAL_TYPE_CHANGE = "Changé";
    const INITIAL_TYPE_NC = "NC";

    public static function getInstance() {
        if(is_null(self::$self)) {

            self::$self = new LotsClient();
        }

        return self::$self;
    }

    public static function getCampagneFromUniqueId($uniqueId) {
        if (!$uniqueId) {
            return null;
        }
        $params = explode('-', $uniqueId);
        return $params[0].'-'.$params[1];
    }

    public static function getNumeroDossierFromUniqueId($uniqueId) {
        if (!$uniqueId) {
            return null;
        }
        $params = explode('-', $uniqueId);

        return $params[2];
    }

    public static function getNumeroArchiveFromUniqueId($uniqueId) {
        if (!$uniqueId) {
            return null;
        }
        $params = explode('-', $uniqueId);

        return $params[3];
    }

    public function getHistory($declarant, $uniqueId)
    {
        $mouvements = MouvementLotHistoryView::getInstance()->getMouvementsByUniqueId($declarant, $uniqueId)->rows;
        $first_mvt = current($mouvements);

        if ($first_mvt && $first_mvt->value && $first_mvt->value->document_id && strpos($first_mvt->value->document_id, "CHGTDENOM") === 0) {
            $lot0_unique_id = ChgtDenomClient::getInstance()->find($first_mvt->value->document_id, acCouchdbClient::HYDRATE_JSON)->changement_origine_lot_unique_id;

            $mvmt_temp = [];
            foreach($this->getHistory($declarant, $lot0_unique_id) as $mvmt) {
                if ($mvmt->value->document_id === $first_mvt->value->document_id) {
                    break;
                }

                $mvmt_temp[] = $mvmt;
            }

            $mouvements = array_merge($mvmt_temp, $mouvements);
        }

        return $mouvements;
    }

    public function getSuivi($declarant, $uniqueId)
    {
        $suivi = ['ORIGINE' => [], 'DEGUSTATION' => [], 'ISSUE' => []];
        $history = $this->getHistory($declarant, $uniqueId);

        foreach($history as $entry) {
            $lot = $entry->value;
            if($lot->lot_unique_id != $uniqueId) {
                continue;
            }
            $lot->date = preg_replace('/ .*$/', '', $lot->date);
            if(empty($suivi['ORIGINE'])) {
                $suivi['ORIGINE']['DATE'] = $lot->date;
                $suivi['ORIGINE']['TYPE'] = $lot->document_type;
                $suivi['ORIGINE']['INITIAL_TYPE'] = $lot->initial_type;
                $suivi['ORIGINE']['STATUT'] = $lot->statut;
                $suivi['ORIGINE']['STATUT_LIBELLE'] = Lot::getLibelleStatut($lot->statut);
                $suivi['ORIGINE']['VOLUME'] = $lot->volume;
            }
            if($lot->statut == Lot::STATUT_CONFORME && $lot->document_type == DegustationClient::TYPE_MODEL) {
                $d = [];
                $d['DATE'] = $lot->date;
                $d['STATUT'] = $lot->statut;
                $d['STATUT_LIBELLE'] = Lot::getLibelleStatut($lot->statut);
                $d['VOLUME'] = $lot->volume;
                $suivi['DEGUSTATION'][] = $d;
                $suivi['ISSUE'] = $d;

                break;
            }

            if($lot->statut == Lot::STATUT_NONCONFORME && $lot->document_type == DegustationClient::TYPE_MODEL) {
                $d = [];
                $d['DATE'] = $lot->date;
                $d['STATUT'] = $lot->statut;
                $d['STATUT_LIBELLE'] = Lot::getLibelleStatut($lot->statut);
                $d['DETAIL'] = explode(" : ", $lot->detail)[0];
                $d['VOLUME'] = $lot->volume;
                $suivi['DEGUSTATION'][] = $d;
                $suivi['ISSUE'] = $d;
            }

            if($lot->statut == Lot::STATUT_CHANGE_DEST) {
                $d = [];
                $d['DATE'] = $lot->date;
                $d['STATUT'] = $lot->statut;
                $d['STATUT_LIBELLE'] = Lot::getLibelleStatut($lot->statut);
                $d['DETAIL'] = $lot->detail;
                $d['VOLUME'] = $lot->volume;
                $suivi['ISSUE'] = $d;
            }

            if($lot->statut == Lot::STATUT_RECOURS_OC) {
                $d = [];
                $d['DATE'] = $lot->date;
                $d['STATUT'] = $lot->statut;
                $d['STATUT_LIBELLE'] = Lot::getLibelleStatut($lot->statut);
                $d['VOLUME'] = $lot->volume;
                $suivi['ISSUE'] = $d;
            }

            if($lot->statut == Lot::STATUT_DECLASSE_OLD) {
                $lot->statut = Lot::STATUT_DECLASSE;
            }

            if( ($lot->statut == Lot::STATUT_MANQUEMENT_EN_ATTENTE) ||
                ($lot->statut == Lot::STATUT_DECLASSE) ||
                ($lot->statut == Lot::STATUT_CONFORME_APPEL) ||
                ($lot->statut == Lot::STATUT_NONCONFORME_LEVEE) ) {
                $d = [];
                $d['DATE'] = $lot->date;
                $d['STATUT'] = $lot->statut;
                $d['STATUT_LIBELLE'] = Lot::getLibelleStatut($lot->statut);
                $d['VOLUME'] = $lot->volume;
                $suivi['ISSUE'] = $d;

                break;
            }

        }

        if(empty($suivi['ISSUE'])) {

            $suivi = null;
        }

        return $suivi;
    }

    public function findByUniqueId($declarantIdentifiant, $uniqueId, $documentOrdre = null) {

        return $this->find($declarantIdentifiant, self::getCampagneFromUniqueId($uniqueId), self::getNumeroDossierFromUniqueId($uniqueId), self::getNumeroArchiveFromUniqueId($uniqueId), $documentOrdre, true);
    }

    public function find($declarantIdentifiant, $campagne, $numeroDossier, $numeroArchive, $documentOrdre = null, $descending = false) {
        $numOrdre = ($documentOrdre)? sprintf("%02d", $documentOrdre) : null;
        $mouvements = MouvementLotHistoryView::getInstance()->getMouvements($declarantIdentifiant, $campagne, $numeroDossier, $numeroArchive, null, $numOrdre, null, $descending);
        $docId = null;
        foreach($mouvements->rows as $mouvement) {
            $docId = $mouvement->id;
            break;
        }

        if(!$docId) {

            return null;
        }

        $doc = DeclarationClient::getInstance()->findCache($docId);

        return $doc->getLot($mouvement->value->lot_unique_id);
    }

    public function getDocumentsIdsByDate($declarantIdentifiant, $uniqueId) {

        $typePriorites = array(
            DRevClient::TYPE_MODEL => "01",
            ConditionnementClient::TYPE_MODEL => "01",
            TransactionClient::TYPE_MODEL => "01",
            PMCClient::TYPE_MODEL => "01",
            TourneeClient::TYPE_MODEL => "02",
            DegustationClient::TYPE_MODEL => "03",
            CourrierClient::TYPE_MODEL => "04",
            PMCNCClient::TYPE_MODEL => "05",
            ChgtDenomClient::TYPE_MODEL => "05",
        );

        $mouvements = MouvementLotHistoryView::getInstance()->getMouvementsByUniqueId($declarantIdentifiant, $uniqueId);

        $documents = array();
        foreach($mouvements->rows as $mouvement) {
            if(in_array($mouvement->id, $documents)) {
                continue;
            }

            $formatDate = $mouvement->value->date;
            $formatDate = substr($formatDate, 0, 10);

            $documents[$formatDate.$typePriorites[$mouvement->value->document_type].$mouvement->key[MouvementLotHistoryView::KEY_DOC_ORDRE].$mouvement->id] = $mouvement->id;
        }

        ksort($documents);

        return $documents;
    }

    public function findStatut($declarantIdentifiant, $uniqueId, $untilDocId) {
        $ids = $this->getDocumentsIdsByDate($declarantIdentifiant, $uniqueId);
        $statut = null;
        foreach($ids as $id) {
            $doc = DeclarationClient::getInstance()->find($id);
            $lot = $doc->getLot($uniqueId);
            if($lot->statut) {
                $statut = $lot->statut;
            }
            if($id == $untilDocId) {
                break;
            }
        }

        return $statut;
    }

    public function getDocumentsIdsByOrdre($declarantIdentifiant, $uniqueId) {
        $mouvements = MouvementLotHistoryView::getInstance()->getMouvementsByUniqueId($declarantIdentifiant, $uniqueId);

        $documents = array();
        foreach($mouvements->rows as $mouvement) {
            $documents[$mouvement->key[MouvementLotHistoryView::KEY_DOC_ORDRE].$mouvement->id] = $mouvement->id;
        }

        ksort($documents);

        return $documents;
    }

    public function modifyAndSave($lot) {
        $ids = $this->getDocumentsIdsByOrdre($lot->declarant_identifiant, $lot->unique_id);

        $nbDegustation = 0;
        foreach($ids as $id) {
            if(preg_match('/(CHGTDENOM)/', $id)) {

                throw new Exception("La modification de lot n'est pas encore implémentée pour les documents de CHGTDENOM");
            }

            if(strpos($id, "DEGUSTATION") !== false) {
                $nbDegustation++;
            }
        }
        if($nbDegustation > 1) {
            throw new Exception("La modification de lot n'est pas possible lorsque que lot a été dégusté plusieurs fois.");
        }

        //On vérifie qu'il est bien possible d'avoir des modificatrices pour tous les id
        $ids2Save = array();
        foreach($ids as $id) {
            $doc = DeclarationClient::getInstance()->find($id);
            if($doc instanceof InterfaceVersionDocument) {
                $master = $doc->getMaster();
                if (!$master->verifyGenerateModificative()) {
                    throw new sfException("il n'est pas possible d'avoir une modificatrice pour le doc ".$id.". Vérifier si une déclaration n'est pas ouverte en brouillon pour cet utilisateur." );
                }
                $ids2Save[$master->_id] = $master->_id;
                continue;
            } elseif($doc->isFactures()) {
                $lotM = $doc->getLot($lot->unique_id);
                if (($lotM->produit_hash != $lot->produit_hash) || ($lotM->volume != $lot->volume) || ($lotM->millesime != $lot->millesime)) {
                    throw new sfException("Le doc ".$id." est déjà facturé modification impossible");
                }
            }
            $ids2Save[$id] = $id;
        }

        foreach($ids2Save as $id) {
            $doc = DeclarationClient::getInstance()->find($id);
            $docM = $doc;

            if($doc instanceof InterfaceVersionDocument) {
                $docM = $doc->getMaster()->generateModificative();
                $docM->numero_archive = $lot->numero_dossier;
            }

            $lotM = $docM->getLot($lot->unique_id);
            $lotM->id_document = $docM->_id;
            $lotM->produit_hash = $lot->produit_hash;
            $lotM->cepages = $lot->cepages;
            $lotM->volume = $lot->volume;
            $lotM->numero_logement_operateur = $lot->numero_logement_operateur;
            $lotM->millesime = $lot->millesime;
            $lotM->destination_type = $lot->destination_type;
            $lotM->destination_date = $lot->destination_date;
            $lotM->specificite = $lot->specificite;

            $docM->save();

            if($docM instanceof InterfaceVersionDocument) {
                $docM->validate();
                $docM->validateOdg();
            }

            $docM->save();
        }
    }

    public function deleteAndSave($declarantIdentifiant, $uniqueId) {
        $lot = LotsClient::getInstance()->findByUniqueId($declarantIdentifiant, $uniqueId);
        $doc = $lot->getDocument();

        if ($lot->getLotAffectation()) {
            throw new sfException("Le lot ".$uniqueId." est déjà dégusté ou changé");
        }

        if($doc instanceof InterfaceVersionDocument) {
            $doc = $doc->getMaster();
            $lot = $doc->getLot($uniqueId);
        }

        if($doc instanceof InterfaceVersionDocument && $doc->isVersionnable()) {
            $doc = $doc->generateModificative();
            $lot = $doc->getLot($uniqueId);
            $doc->numero_archive = $lot->numero_dossier;
            $generateModificative = true;
        }

        if(!$lot) {
            throw new Exception("Le lot ".$uniqueId." n'existe pas dans le doc ".$doc->_id);
        }

        if($doc->isFactures()) {
            throw new Exception("Le document ".$doc->_id." est déjà facturé");
        }

        $doc->remove($lot->getHash());
        $doc->save();

        if(isset($generateModificative)) {
            $doc->validate();
            $doc->validateOdg();
            $doc->save();
        }
    }

    /*************
     *
     * return: [Appellation Libelle][periode][CouleurLibelle][DocType]
     *
     * DocType a été introduit pour centre loire : l'index "Lot" rend le résultat retourné précédemment
     *************/

    public function getSyntheseLots($identifiant, $campagnes, $region = null)
    {
        if(!is_array($campagnes)) {
            $campagnes = [$campagnes];
        }
        $mouvements = [];
        foreach($campagnes as $campagne) {
            $mouvements = array_merge($mouvements, MouvementLotHistoryView::getInstance()->getMouvementsByDeclarant($identifiant, $campagne, $region)->rows);
        }

        if ($region) {
            $mouvements = RegionConfiguration::getInstance()->filterMouvementsByRegion($mouvements, $region);
        }

        $syntheseLots = MouvementLotHistoryView::getInstance()->buildSyntheseLots($mouvements);

        foreach($campagnes as $campagne) {
            $drev = DRevClient::getInstance()->findMasterByIdentifiantAndCampagne($identifiant, $campagne);

            if($drev) {
                foreach($drev->declaration->getProduitsWithoutLots() as $produit) {
                    if($region && $produit->getRegion() != $region) {
                        continue;
                    }
                    @$syntheseLots[$produit->getConfig()->getAppellation()->getLibelle()][$drev->periode][$produit->getConfig()->getCouleur()->getLibelle()]["DRev"] += $produit->volume_revendique_issu_recolte;
                    if($produit->volume_revendique_issu_vci) {
                        @$syntheseLots[$produit->getConfig()->getAppellation()->getLibelle()][$drev->periode - 1][$produit->getConfig()->getCouleur()->getLibelle()]["DRev"] += $produit->volume_revendique_issu_vci;
                        @$syntheseLots[$produit->getConfig()->getAppellation()->getLibelle()][$drev->periode - 1][$produit->getConfig()->getCouleur()->getLibelle()]["DREVVCI"] += $produit->volume_revendique_issu_vci;
                    }
                }
            }
        }

        return $syntheseLots;
    }

    public function getLastMouvements($doc)
    {
        if (empty($this->mouvements_declarant) === false) {
            return $this->mouvements_declarant;
        }

        foreach (MouvementLotHistoryView::getInstance()->getMouvementsByDeclarant($doc->identifiant, $doc->campagne)->rows as $mouvement) {
            $this->mouvements_declarant[
                $mouvement->key[MouvementLotHistoryView::KEY_NUMERO_DOSSIER] . $mouvement->key[MouvementLotHistoryView::KEY_NUMERO_ARCHIVE]
            ] = $mouvement->value;
        }

        return $this->mouvements_declarant;
    }

    public function saisieMentionCepageActive() {
        return sfConfig::get('app_donnees_viticoles_lot_saisie_mention_cepage_active', true);
    }
}
