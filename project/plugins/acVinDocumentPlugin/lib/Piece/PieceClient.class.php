<?php

class PieceClient {

    public static function find($docId) {
        $doc = acCouchdbManager::getClient()->find($docId);
        if(!$doc) {
            throw new sfException("Document $docId not found");
        }
        if (!$doc->exist('pieces')) {
            throw new sfException("No piece found for document $docId");
        }
        return $doc;
    }

    public static function findUrlByIdAndPiece($docId, $pieceId, $fileParam) {
        $doc = self::find($docId);
        return self::getUrlForPieceId($doc, $pieceId, $fileParam);
    }

    public static function getUrlForPieceId($doc, $pieceId, $fileParam) {
        if (!$doc->pieces->exist($pieceId)) {
            throw new sfException("Piece $pieceId in document $docId not found");
        }
        $piece = $doc->pieces->get($pieceId);
        $url = $piece->getUrl();
        return ($fileParam) ? $url.'?file='.$fileParam : $url;
    }

    public static function getPDFDataByDocId($docId) {
        $doc = self::find($docId);
        $pieceId = null;
        $filename = null;
        foreach($doc->pieces as $id => $p) {
            $pieceId = $id;
            foreach($p->fichiers as $f) {
                if (strpos($f, '.pdf') !== false) {
                    $filename = $f;
                }
            }
        }
        if ($pieceId === null || $filename === null) {
            throw new sfException("not pdf found id $docId");
        }
        return file_get_contents($doc->getAttachmentUri($filename));
    }

}