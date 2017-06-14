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

    	return $this->redirect('declaration_etablissement', $this->etablissement);
    }

	public function executePiecesHistorique(sfWebRequest $request) {
		$this->etablissement = $this->getUser()->getEtablissement();
		$this->year = $request->getParameter('annee', 0);
		$this->category = $request->getParameter('categorie');

		$allHistory = PieceAllView::getInstance()->getPiecesByEtablissement($this->etablissement->identifiant);
		$this->history = ($this->year)? PieceAllView::getInstance()->getPiecesByEtablissement($this->etablissement->identifiant, $this->year.'-01-01', $this->year.'-12-31') : $allHistory;
		$this->years = array();
		$this->categories = array();
		$this->decreases = 0;
		foreach ($allHistory as $doc) {
			if (preg_match('/^([0-9]{4})-[0-9]{2}-[0-9]{2}$/', $doc->key[PieceAllView::KEYS_DATE_DEPOT], $m)) {
				$this->years[$m[1]] = $m[1];
			}
			if ($this->year && (!isset($m[1]) || $m[1] != $this->year)) { continue; }
			if (preg_match('/^([a-zA-Z]*)\-./', $doc->id, $m)) {
				if ($this->year && $m[1] == 'FICHIER') { $this->decreases++; continue; }
				if (!isset($this->categories[$m[1]])) {
					$this->categories[$m[1]] = 0;
				}
				$this->categories[$m[1]]++;
			}
		}
		ksort($this->categories);
	}

}
