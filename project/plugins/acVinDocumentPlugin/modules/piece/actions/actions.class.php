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

    public function executePublicView(sfWebRequest $request) {
        $docId = $request->getParameter('doc_id');
    	$pieceId = str_replace('-', '/', $request->getParameter('piece_id'));
        $authKey = $request->getParameter('auth');

        if (!UrlSecurity::verifyAuthKey($authKey, $docId.$pieceId)) {
            throw new sfError403Exception("Vous n'avez pas le droit d'accéder à cette page");
        }

        $this->piece = PieceClient::getInstance()->getPiece($docId, $pieceId);
        if(!Piece::hasUrlPublic($docId)) {

            throw new sfError404Exception();
        }

        $this->getResponse()->addMeta('robots', 'noindex');
        $this->setLayout(false);
    }
}
