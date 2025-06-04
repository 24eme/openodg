<?php

class fichierActions extends sfActions
{
	public function executeIndex(sfWebRequest $request) {
		if(class_exists("EtablissementChoiceForm")) {
			$this->form = new EtablissementChoiceForm('INTERPRO-declaration', array(), true);
        } elseif(class_exists("LoginForm")) {
            $this->form = new LoginForm();
        }
	}

	public function executeMesdocuments(sfWebRequest $request) {

		if(!$this->getUser()->hasTeledeclaration()) {
			return $this->forwardSecure();
		}

		return $this->redirect('accueil', array('redirect' => 'documents'));
	}

	public function executeEtablissementSelection(sfWebRequest $request) {
        $form = new EtablissementChoiceForm('INTERPRO-declaration', array(), true);
        $form->bind($request->getParameter($form->getName()));
        if (!$form->isValid()) {

            return $this->redirect('documents');
        }

        return $this->redirect('pieces_historique', $form->getEtablissement());
    }

	public function executeGet(sfWebRequest $request) {
    	$fichier = $this->getRoute()->getFichier();
    	$fileParam = $request->getParameter('file', null);

		$this->secureEtablissement($fichier->getEtablissementObject());
		if(!$fichier->visibilite && !$this->getUser()->hasCredential(myUser::CREDENTIAL_ADMIN) && !$this->getUser()->hasCredential(myUser::CREDENTIAL_HABILITATION)) {

			throw new sfError403Exception();
		}

		if($this->getCategoriesLimitation() && !in_array($fichier->categorie, $this->getCategoriesLimitation())) {

			throw new sfError403Exception();
		}

    	if (!$fichier->hasFichiers()) {
    		return $this->forward404("Aucun fichier pour ".$fichier->_id);
    	}
    	$filename = null;
		foreach ( array_filter([$fileParam, 'pdf', 'xls', 'csv'], 'is_string') as $fileParam_test) {
    		foreach ($fichier->_attachments as $key => $attachment) {
            	if (!$fileParam_test || strpos($key, $fileParam_test) !== false ) {
    				$filename = $key;
					break 2;
    			}
			}
    	}
		if (!$filename) {
			$filename = $key;
		}
        if (!$fileParam) {
            $fileParam = $filename;
        }
    	$file = file_get_contents($fichier->getAttachmentUri($filename));
        if(!$file) {
            return $this->forward404($filename." n'existe pas pour ".$fichier->_id);
        }
        $this->getResponse()->setHttpHeader('Content-Type', $fichier->getMime($fileParam));
        $this->getResponse()->setHttpHeader('Content-disposition', sprintf('attachment; filename="%s-%s-%s"', strtoupper($fichier->type), $fichier->getIdentifiant(), $filename));
        $this->getResponse()->setHttpHeader('Content-Transfer-Encoding', 'binary');
        $this->getResponse()->setHttpHeader('Pragma', '');
        $this->getResponse()->setHttpHeader('Cache-Control', 'public');
        $this->getResponse()->setHttpHeader('Expires', '0');

        return $this->renderText($file);
    }

    public function executeDelete(sfWebRequest $request) {
    	$fichier = $this->getRoute()->getFichier();
        $etablissement = $fichier->getEtablissementObject();
    	$fichier->deleteFichier($request->getParameter('file', null));
    	$fichier->save();
    	if (!$fichier->getNbFichier()) {
    		$fichier->delete();
    		return $this->redirect('declaration_etablissement', array('identifiant' => $etablissement->identifiant));
    	}
    	return $this->redirect('upload_fichier', array('fichier_id' => $fichier->_id, 'sf_subject' => $fichier->getEtablissementObject()));
    }

    public function executeCsvgenerate(sfWebRequest $request) {
    	$fichier = $this->getRoute()->getFichier();
    	$csv = "";
    	if (preg_match('/^([a-zA-Z0-9]+)-.*$/', $fichier->_id, $m)) {
    		$className = DeclarationClient::getInstance()->getExportCsvClassName($m[1]);
    		$csvOrigine = new $className($fichier);
    		$csv .= $csvOrigine->export();
    	}
    	$this->getResponse()->setHttpHeader('Content-Type', 'text/csv');
    	$this->getResponse()->setHttpHeader('Content-disposition', sprintf('attachment; filename="%s.csv"', $fichier->_id));
    	$this->getResponse()->setHttpHeader('Content-Transfer-Encoding', 'binary');
    	$this->getResponse()->setHttpHeader('Pragma', '');
    	$this->getResponse()->setHttpHeader('Cache-Control', 'public');
    	$this->getResponse()->setHttpHeader('Expires', '0');

    	return $this->renderText($csv);
    }

    public function executeUpload(sfWebRequest $request) {
        $this->etablissement = $this->getRoute()->getEtablissement(['allow_habilitation' => true]);

		if($request->getParameter('fichier_id') && !$this->getUser()->isAdmin()) {

			throw new sfError403Exception();
		}

    	$this->fichier_id = $request->getParameter('fichier_id');

    	$this->fichier = ($this->fichier_id) ? FichierClient::getInstance()->find($this->fichier_id) : null;
        if (!$this->fichier) {
            $this->fichier = FichierClient::getInstance()->createDoc($this->etablissement->identifiant, true);
        }

		$categories = null;

		if(!$this->getUser()->isAdmin() && $this->getUser()->hasCredential(myUser::CREDENTIAL_OI)) {
			$categories = array(FichierClient::CATEGORIE_OI => FichierClient::CATEGORIE_OI);
		}else if(!$this->getUser()->isAdmin() && $this->getUser()->hasCredential(myUser::CREDENTIAL_HABILITATION)) {
			$categories = array(FichierClient::CATEGORIE_IDENTIFICATION => FichierClient::CATEGORIE_IDENTIFICATION);
		}

    	$this->form = new FichierForm($this->fichier, null, array('categories' => $categories));

    	if (!$request->isMethod(sfWebRequest::POST)) {
    		return sfView::SUCCESS;
    	}

    	$this->form->bind($request->getParameter($this->form->getName()), $request->getFiles($this->form->getName()));

    	if (!$this->form->isValid()) {
    		return sfView::SUCCESS;
    	}

    	$this->form->save();
        if ($request->hasParameter('keep_page')) {
            return $this->redirect('upload_fichier', array('fichier_id' => $this->fichier->_id, 'identifiant' => $this->etablissement->identifiant));
        }
        return $this->redirect('pieces_historique', array('identifiant' => $this->etablissement->identifiant));
    }

	protected function getCategoriesLimitation() {

		return null;
	}

	public function executePiecesHistorique(sfWebRequest $request) {
		$this->etablissement = $this->getRoute()->getEtablissement(['allow_stalker' => true, 'allow_habilitation' => true]);
		$this->societe = $this->etablissement->getSociete();
		$this->secureEtablissement($this->etablissement);

		$this->campagne = $request->getParameter('campagne', 0);
        if ($this->campagne) {
            $startdate = substr($this->campagne, 0, 4);
            $enddate = substr($this->campagne, -4, 4);
        }

		$this->category = $request->getParameter('categorie');

		$this->categoriesLimitation = $this->getCategoriesLimitation();

		$visibilite = $this->getUser()->hasCredential(myUser::CREDENTIAL_ADMIN) || $this->getUser()->hasCredential(myUser::CREDENTIAL_HABILITATION);

		$piecesSocietes = array();

		if($this->societe) {
			$piecesSocietes = PieceAllView::getInstance()->getPiecesByEtablissement($this->societe->identifiant, $visibilite, null, null, $this->categoriesLimitation);
		}

		$allHistory = array_merge(
										PieceAllView::getInstance()->getPiecesByEtablissement($this->etablissement->identifiant, $visibilite, null, null, $this->categoriesLimitation),
										$piecesSocietes
									);

		$this->history = ($this->campagne)? PieceAllView::getInstance()->getPiecesByEtablissement($this->etablissement->identifiant, $visibilite, $startdate.'-08-01', $enddate.'-07-31', $this->categoriesLimitation) : $allHistory;

		$this->campagnes = array();
		$this->categories = array();
		$this->decreases = 0;
		foreach ($allHistory as $doc) {

            if(is_bool($doc->key[PieceAllView::KEYS_DATE_DEPOT])) {
                $doc->key[PieceAllView::KEYS_DATE_DEPOT] = null;
                $date = null;
            }

            if($doc->key[PieceAllView::KEYS_DATE_DEPOT]) {
                $date = DateTime::createFromFormat('Y-m-d', preg_replace('/ .*/', '', $doc->key[PieceAllView::KEYS_DATE_DEPOT]));
            }

            if ($date && $date < DateTime::createFromFormat('Y-m-d', $date->format('Y').'-08-01')) {
                $end_campagne = $date->format('Y');
                $start_campagne = $date->modify('-1 year')->format('Y');
                $this->campagnes[$start_campagne.'-'.$end_campagne] = $start_campagne.'-'.$end_campagne;
            } elseif($date) {
                $start_campagne = $date->format('Y');
                $end_campagne = $date->modify('+1 year')->format('Y');
                $this->campagnes[$start_campagne.'-'.$end_campagne] = $start_campagne.'-'.$end_campagne;
            }

			$categorie = strtolower($doc->key[PieceAllView::KEYS_CATEGORIE]);
			if (!isset($this->categories[$categorie])) {
				$this->categories[$categorie] = 0;
			}
			$this->categories[$categorie]++;
		}
        uasort($this->history, function($a, $b) { return $a->key[PieceAllView::KEYS_DATE_DEPOT] < $b->key[PieceAllView::KEYS_DATE_DEPOT]; });

		ksort($this->categories);
	}

	public function executeEdit(sfWebRequest $request) {
    	$this->fichier = $this->getRoute()->getFichier();

        if ($this->fichier->isFactures()) {
            throw new Exception($this->fichier->getType().' déjà facturée');
        }

        $this->etablissement = $this->fichier->getEtablissementObject();

        $this->fichier->generateDonnees();

        $this->form = new FichierDonneesForm($this->fichier);

        if (!$request->isMethod(sfWebRequest::POST)) {
        	return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

        if (!$this->form->isValid()) {
        	return sfView::SUCCESS;
        }

        $this->form->save();

        $this->getUser()->setFlash("notice", "Modifications prises en compte avec succès.");

        return $this->redirect($this->generateUrl('edit_fichier', $this->fichier));
	}

	public function executeNew(sfWebRequest $request) {
    	$this->etablissement = $this->getRoute()->getEtablissement();
    	$this->periode = $request->getParameter('periode');
    	$this->type = $request->getParameter('type');

    	if (!$this->periode) {
    		return $this->forward404("La création d'un fichier nécessite la periode");
    	}

    	if (!$this->type) {
    		return $this->forward404("La création d'un fichier nécessite le type");
    	}

    	$client = $this->type.'Client';
    	if ($doc = $client::getInstance()->findByArgs($this->etablissement->identifiant, $this->periode)) {
    		return $this->redirect($this->generateUrl('edit_fichier', $doc));
    	}

        $doc = $client::getInstance()->createDoc($this->etablissement->identifiant, $this->periode, true);
        if ($doc->exist('libelle')) $doc->libelle = $this->type.' '.$this->periode.' saisie interne';
        if ($doc->exist('visibilite')) $doc->visibilite = 0;
        if ($doc->exist('date_depot')) $doc->date_depot = date('Y-m-d');
        if ($doc->exist('date_import')) $doc->date_import = date('Y-m-d');
        $doc->save();

        return $this->redirect($this->generateUrl('edit_fichier', $doc));
	}

	public function executeScrape(sfWebRequest $request) {
		$this->etablissement = $this->getRoute()->getEtablissement();
		$this->periode = $request->getParameter('periode');
		$this->type = $request->getParameter('type');

		if ($request->isMethod(sfWebRequest::POST)) {
			try {
				$fichiers = FichierClient::getInstance()->scrapeAndSaveFiles($this->etablissement, $this->type, $this->periode);
			    $drev = DRevClient::getInstance()->findMasterByIdentifiantAndPeriode($this->etablissement->identifiant, $this->periode);
				if ($fichiers && $drev) {
					$drev->resetAndImportFromDocumentDouanier();
                	$drev->save();
				}
			}catch(sfException $e ) {
				$this->getUser()->setFlash('error', $e->getMessage());
				return $this->redirect('declaration_etablissement', array('identifiant' => $this->etablissement->identifiant, "campagne" => sprintf('%d-%d', $this->periode, ($this->periode + 1)) ));
			}
			return $this->redirect('dr_redirect', array('identifiant' => $this->etablissement->identifiant, 'campagne' => sprintf('%d-%d', $this->periode, ($this->periode + 1)) ));
		}

	}

	protected function secureEtablissement($etablissement) {
        if (class_exists("AppUser") && !$this->getUser()->hasHabilitation() && !$this->getUser()->hasCredential(AppUser::CREDENTIAL_STALKER) && !EtablissementSecurity::getInstance($this->getUser(), $etablissement)->isAuthorized(array())) {

            return $this->forwardSecure();
        }
    }

    protected function forwardSecure() {
        $this->context->getController()->forward(sfConfig::get('sf_secure_module'), sfConfig::get('sf_secure_action'));

        throw new sfStopException();
    }

}
