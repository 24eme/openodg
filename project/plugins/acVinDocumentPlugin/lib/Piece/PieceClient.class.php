<?php

class PieceClient extends acCouchdbClient {

    public static function getInstance() {
        return acCouchdbManager::getClient("Piece");
    }

    public function find($id, $hydrate = self::HYDRATE_DOCUMENT, $force_return_ls = false) {
        $doc = parent::find($id);
        if(!$doc) {
            throw new sfException("Document $id not found");
        }
        if (!$doc->exist('pieces')) {
            throw new sfException("No piece found for document $id");
        }
        return $doc;
    }

    public function getPiece($docId, $pieceId) {
        $doc = $this->find($docId);
        if (!$doc->pieces->exist($pieceId)) {
            throw new sfException("Piece $pieceId in document $docId not found");
        }

        return $doc->pieces->get($pieceId);
    }

    public function getPieceBySource($docId, $source) {
        $doc = $this->find($docId);

        $pieceFound = null;
        foreach($doc->pieces as $piece) {
            if($piece->source != $source) {
                continue;
            }
            $pieceFound = $piece;

            break;
        }

        return $pieceFound;
    }

    public function findUrlByIdAndPiece($docId, $pieceId, $fileParam) {

        return $this->getUrlForPieceId($docId, $pieceId, $fileParam);
    }

    public function getUrlForPieceId($docId, $pieceId, $fileParam) {
        $url = $this->getPiece($docId, $pieceId)->getUrl();

        return ($fileParam) ? $url.'?file='.$fileParam : $url;
    }

    public function getFileContentsByDocIdAndTypes($docId, $types = array('.pdf', '.jpg', '.png')) {
        $doc = $this->find($docId);
        $pieceId = null;
        $filename = null;
        foreach($doc->pieces as $id => $p) {
            $pieceId = $id;
            foreach($p->fichiers as $f) {
                foreach($types as $type) {
                    if (strpos($f, $type) !== false) {
                        $filename = $f;
                        break 3;
                    }
                }
            }
        }
        if ($pieceId === null || $filename === null) {
            throw new sfException("not pdf found id $docId");
        }
        $mime = $doc->_attachments->get($filename)->content_type;
        return array('file_data' => file_get_contents($doc->getAttachmentUri($filename)), 'file_name' => $filename, 'file_mime' => $mime);
    }

}