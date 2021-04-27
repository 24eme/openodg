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

    public function getDocumentsIds($declarantIdentifiant, $campagne, $numeroDossier, $numeroArchive) {
        $mouvements = MouvementLotHistoryView::getInstance()->getMouvements($declarantIdentifiant, $campagne, $numeroDossier, $numeroArchive);

        $documents = array();
        foreach($mouvements->rows as $mouvement) {
            $documents[$mouvement->key[MouvementLotHistoryView::KEY_DOC_ORDRE].$mouvement->id] = $mouvement->id;
        }

        ksort($documents);

        return $documents;
    }

    public function modifyAndSave($lot) {
        $ids = $this->getDocumentsIds($lot->declarant_identifiant, $lot->campagne, $lot->numero_dossier, $lot->numero_archive);

        foreach($ids as $id) {
            $doc = DeclarationClient::getInstance()->find($id);

            if($doc instanceof InterfaceVersionDocument) {
                $doc = $doc->generateModificative();
            }

            $lotM = $doc->getLot($lot->unique_id);
            $lotM->id_document = $doc->_id;
            $lotM->volume = $lot->volume;

            if($doc instanceof InterfaceVersionDocument) {
                $doc->validate();
                $doc->validateOdg();
            }

            $doc->save();
        }
    }

}