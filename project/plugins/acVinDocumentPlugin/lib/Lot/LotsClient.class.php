<?php

class LotsClient
{
    protected static $self = null;

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

    public function findByUniqueId($declarantIdentifiant, $uniqueId, $documentOrdre = "01") {

        return $this->find($declarantIdentifiant, self::getCampagneFromUniqueId($uniqueId), self::getNumeroDossierFromUniqueId($uniqueId), self::getNumeroArchiveFromUniqueId($uniqueId), $documentOrdre);
    }

    public function find($declarantIdentifiant, $campagne, $numeroDossier, $numeroArchive, $documentOrdre = "01") {
        $mouvements = MouvementLotHistoryView::getInstance()->getMouvements($declarantIdentifiant, $campagne, $numeroDossier, $numeroArchive, sprintf("%02d", $documentOrdre));
        $docId = null;
        foreach($mouvements->rows as $mouvement) {
            $docId = $mouvement->id;
            break;
        }

        if(!$docId) {

            return null;
        }

        $doc = DeclarationClient::getInstance()->findCache($docId);

        return $doc->get($mouvement->value->lot_hash);
    }

    public function getDocumentsIds($declarantIdentifiant, $uniqueId) {
        $mouvements = MouvementLotHistoryView::getInstance()->getMouvementsByUniqueId($declarantIdentifiant, $uniqueId);

        $documents = array();
        foreach($mouvements->rows as $mouvement) {
            $documents[$mouvement->key[MouvementLotHistoryView::KEY_DOC_ORDRE].$mouvement->id] = $mouvement->id;
        }

        ksort($documents);

        return $documents;
    }

    public function modifyAndSave($lot) {
        $ids = $this->getDocumentsIds($lot->declarant_identifiant, $lot->unique_id);

        foreach($ids as $id) {
            if(preg_match('/(TRANSACTION|CONDITIONNEMENT|CHGTDENOM)/', $id)) {

                throw new Exception("La modification de lot n'est pas encore implémentée pour les documents de TRANSACTION, CONDITIONNEMENT et CHGTDENOM");
            }
        }

        foreach($ids as $id) {
            $doc = DeclarationClient::getInstance()->find($id);

            if($doc instanceof InterfaceVersionDocument) {
                $doc = $doc->getMaster()->generateModificative();
            }

            $lotM = $doc->getLot($lot->unique_id);
            $lotM->id_document = $doc->_id;
            $lotM->produit_hash = $lot->produit_hash;
            $lotM->cepages = $lot->cepages;
            $lotM->volume = $lot->volume;
            $lotM->numero_logement_operateur = $lot->numero_logement_operateur;
            $lotM->millesime = $lot->millesime;
            $lotM->destination_type = $lot->destination_type;
            $lotM->destination_date = $lot->destination_date;
            $lotM->specificite = $lot->specificite;

            if($doc instanceof InterfaceVersionDocument) {
                $doc->validate();
                $doc->validateOdg();
            }

            $doc->save();
        }
    }

}