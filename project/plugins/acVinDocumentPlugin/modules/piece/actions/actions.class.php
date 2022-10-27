<?php
class pieceActions extends sfActions
{
    public function executeGet(sfWebRequest $request) {
    	$docId = $request->getParameter('doc_id');
    	$pieceId = str_replace('-', '/', $request->getParameter('piece_id'));
    	$fileParam = $request->getParameter('file', null);
        try {
			return $this->redirect(PieceClient::getInstance()->findUrlByIdAndPiece($docId, $pieceId, $fileParam));
		} catch (sfException $e) {
			return $this->forward404($e->getMessage());
		}
    }
}