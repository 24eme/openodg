<?php

class fichierActions extends sfActions
{
	public function executeGet(sfWebRequest $request) {
    	$fichier = $this->getRoute()->getFichier();
    	if (!$fichier->hasAttachments()) {
    		return $this->forward404("Aucun fichier pour ".$fichier->_id);
    	}
    	$filename = null;
    	foreach ($fichier->_attachments as $key => $attachment) {
    		$filename = $key;
    	}
    	$file = file_get_contents($fichier->getAttachmentUri($filename));
        if(!$file) {
            return $this->forward404($filename." n'existe pas pour ".$fichier->_id);
        }

        $this->getResponse()->setHttpHeader('Content-Type', $fichier->getMime());
        $this->getResponse()->setHttpHeader('Content-disposition', sprintf('attachment; filename="FICHIER-%s-%s"', $fichier->getIdentifiant(), $filename));
        $this->getResponse()->setHttpHeader('Content-Transfer-Encoding', 'binary');
        $this->getResponse()->setHttpHeader('Pragma', '');
        $this->getResponse()->setHttpHeader('Cache-Control', 'public');
        $this->getResponse()->setHttpHeader('Expires', '0');

        return $this->renderText($file);
    }
    
    public function executeUpload(sfWebRequest $request) {
    	$this->etablissement = $this->getUser()->getEtablissement();
    	$this->fichier_id = $request->getParameter('fichier_id');
    	$this->fichier = ($this->fichier_id) ? FichierClient::getInstance()->find($this->fichier_id) : FichierClient::getInstance()->createDoc($this->etablissement->identifiant, true);
    	$this->form = new FichierForm($this->fichier);
    	
    	if (!$request->isMethod(sfWebRequest::POST)) {
    		return sfView::SUCCESS;
    	}

    	$this->form->bind($request->getParameter($this->form->getName()), $request->getFiles($this->form->getName()));
    	
    	if (!$this->form->isValid()) {
    		return sfView::SUCCESS;
    	}
    	
    	$this->form->save();
    	
    	return $this->redirect('home');
    }

}
