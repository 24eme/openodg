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

    public function modifyAndSave($lot) {
        $docM = $lot->getDocument()->generateModificative();
        $lotM = $docM->getLot($lot->unique_id);
        $lotM->volume = $lot->volume;
        $docM->validate();
        $docM->validateOdg();
        $docM->save();
    }

}