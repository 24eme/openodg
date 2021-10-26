<?php

class LotsClient
{
    protected static $self = null;

    const INITIAL_TYPE_CHANGE = "Changé";

    public static function getInstance() {
        if(is_null(self::$self)) {

            self::$self = new LotsClient();
        }

        return self::$self;
    }

    public static function getCampagneFromUniqueId($uniqueId) {
        $params = explode('-', $uniqueId);
        return $params[0].'-'.$params[1];
    }

    public static function getNumeroDossierFromUniqueId($uniqueId) {
        $params = explode('-', $uniqueId);

        return $params[2];
    }

    public static function getNumeroArchiveFromUniqueId($uniqueId) {
        $params = explode('-', $uniqueId);

        return $params[3];
    }

    public function getHistory($declarant, $uniqueId)
    {
        $mouvements = MouvementLotHistoryView::getInstance()->getMouvementsByUniqueId($declarant, $uniqueId)->rows;
        $first_mvt = current($mouvements);

        if (strpos($first_mvt->value->document_id, "CHGTDENOM") === 0) {
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

    public function findByUniqueId($declarantIdentifiant, $uniqueId, $documentOrdre = null) {

        return $this->find($declarantIdentifiant, self::getCampagneFromUniqueId($uniqueId), self::getNumeroDossierFromUniqueId($uniqueId), self::getNumeroArchiveFromUniqueId($uniqueId), $documentOrdre, true);
    }

    public function find($declarantIdentifiant, $campagne, $numeroDossier, $numeroArchive, $documentOrdre = null, $descending = false) {
        $numOrdre = ($documentOrdre)? sprintf("%02d", $documentOrdre) : null;
        $mouvements = MouvementLotHistoryView::getInstance()->getMouvements($declarantIdentifiant, $campagne, $numeroDossier, $numeroArchive, $numOrdre, null, $descending);
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
            DegustationClient::TYPE_MODEL => "02",
            ChgtDenomClient::TYPE_MODEL => "02",
        );

        $mouvements = MouvementLotHistoryView::getInstance()->getMouvementsByUniqueId($declarantIdentifiant, $uniqueId);

        $documents = array();
        foreach($mouvements->rows as $mouvement) {
            if(in_array($mouvement->id, $documents)) {
                continue;
            }
            $documents[$typePriorites[$mouvement->value->document_type].$mouvement->value->date.$mouvement->key[MouvementLotHistoryView::KEY_DOC_ORDRE].$mouvement->id] = $mouvement->id;
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
            if(preg_match('/(TRANSACTION|CONDITIONNEMENT|CHGTDENOM)/', $id)) {

                throw new Exception("La modification de lot n'est pas encore implémentée pour les documents de TRANSACTION, CONDITIONNEMENT et CHGTDENOM");
            }

            if(strpos($id, "DEGUSTATION") !== false) {
                $nbDegustation++;
            }
        }
        if($nbDegustation > 1) {
            throw new Exception("La modification de lot n'est pas possible lorsque que lot a été dégusté plusieurs fois.");
        }

        //On vérifie qu'il est bien possible d'avoir des modificatrices pour tous les id
        foreach($ids as $id) {
            $doc = DeclarationClient::getInstance()->find($id);
            if($doc instanceof InterfaceVersionDocument) {
                if (!$doc->getMaster()->verifyGenerateModificative()) {
                    throw new sfException("il n'est pas possible d'avoir une modificatrice pour le doc ".$id);
                }
            }
        }

        foreach($ids as $id) {
            $doc = DeclarationClient::getInstance()->find($id);
            $docM = $doc;

            if($doc instanceof InterfaceVersionDocument) {
                $docM = $doc->getMaster()->generateModificative();
                $docM->numero_archive = $doc->numero_archive;
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

}
