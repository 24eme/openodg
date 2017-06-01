<?php

class avaActions extends sfActions {

    public function executeHome(sfWebRequest $request) {

        if ($this->getUser()->hasCredential(myUser::CREDENTIAL_TOURNEE)) {

            return $this->redirect('tournee_agent_accueil');
        }

        if ($this->getUser()->hasCredential(myUser::CREDENTIAL_ADMIN)) {
            $this->formLogin = new LoginForm();
        }


        if ($this->getUser()->hasCredential(myUser::CREDENTIAL_ADMIN) && $request->isMethod(sfWebRequest::POST)) {
            $this->formLogin->bind($request->getParameter($this->formLogin->getName()));
            if ($this->formLogin->isValid()) {
                $this->getUser()->signInEtablissement($this->formLogin->getValue('etablissement'));
                return $this->redirect('home');
            }
        }

        $this->date_ouverture_drev = sfConfig::get('app_date_ouvertures_drev');
        $this->date_ouverture_drevmarc = sfConfig::get('app_date_ouvertures_drevmarc');

        $this->drev_non_ouverte = false;
        $this->drevmarc_non_ouverte = false;

        if (null !== $this->date_ouverture_drev) {
            if (str_replace('-', '', $this->date_ouverture_drev) >= date('Ymd')) {
                $this->drev_non_ouverte = true;
            }
        }

        if (null !== $this->date_ouverture_drevmarc) {
            if (str_replace('-', '', $this->date_ouverture_drevmarc) >= date('Ymd')) {
                $this->drevmarc_non_ouverte = true;
            }
        }

        $this->etablissement = $this->getUser()->getEtablissement();

        if (!$this->etablissement && $this->getUser()->hasCredential(myUser::CREDENTIAL_ADMIN)) {

            return $this->redirect('admin');
        } if (!$this->etablissement && $this->getUser()->hasCredential(myUser::CREDENTIAL_CONTACT)) {

            return $this->redirect('compte_recherche');
        } elseif (!$this->etablissement) {

            return $this->forwardSecure();
        }

        $this->form = new EtablissementConfirmationEmailForm($this->etablissement);

        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

        if (!$this->form->isValid()) {

            return sfView::SUCCESS;
        }

        $this->form->save();

        return $this->redirect('home');
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

    public function executeContact(sfWebRequest $request) {

    }

    public function executeMentionsLegales(sfWebRequest $request) {

    }

    protected function forwardSecure() {
        $this->context->getController()->forward(sfConfig::get('sf_secure_module'), sfConfig::get('sf_secure_action'));

        throw new sfStopException();
    }

}
