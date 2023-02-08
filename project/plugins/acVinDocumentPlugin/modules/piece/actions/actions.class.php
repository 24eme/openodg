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
        $source = $request->getParameter('source');
        $authKey = $request->getParameter('auth');

        if (!UrlSecurity::verifyAuthKey($authKey, $docId.$source)) {
            throw new sfError403Exception("Vous n'avez pas le droit d'accéder à cette page");
        }

        if(!Piece::hasUrlPublic($docId)) {

            throw new sfError404Exception();
        }

        $this->piece = PieceClient::getInstance()->getPieceBySource($docId, $source);

        if(!$this->piece) {

            throw new sfError404Exception();
        }

        $this->getResponse()->addMeta('robots', 'noindex');
        $this->setLayout(false);
    }
}
