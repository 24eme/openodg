<?php
class pieceActions extends sfActions 
{
    public function executeGet(sfWebRequest $request) {
    	$docId = $request->getParameter('doc_id');
    	$pieceId = str_replace('-', '/', $request->getParameter('piece_id'));
    	$client = acCouchdbManager::getClient();
    	if ($doc = $client->find($docId)) {
    		if ($doc->exist('pieces') && $doc->pieces->exist($pieceId)) {
    			$piece = $doc->pieces->get($pieceId);
    			return $this->redirect($piece->getUrl());
    		} else {
    			return $this->forward404("Pièce $pieceId not found");
    		}
    	} else {
    		return $this->forward404("Document $docId not found");
    	}
    }
}