<?php

class declarationActions extends sfActions {

    public function executeIndex(sfWebRequest $request) {
        $this->buildSearch($request);
        $nbResultatsParPage = 15;
        $this->nbResultats = count($this->docs);
        $this->page = $request->getParameter('page', 1);
        $this->nbPage = ceil($this->nbResultats / $nbResultatsParPage);
        $this->docs = array_slice($this->docs, ($this->page - 1) * $nbResultatsParPage, $nbResultatsParPage);

        if(class_exists("EtablissementChoiceForm")) {
            $this->form = new EtablissementChoiceForm('INTERPRO-declaration', array(), true);
        } elseif(class_exists("LoginForm")) {
            $this->form = new LoginForm();
        }

        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

        if(!$this->form->isValid()) {

            return sfView::SUCCESS;
        }

        return $this->redirect('declaration_etablissement', $this->form->getValue('etablissement'));
    }

    public function executeDoc(sfWebRequest $request) {
        $doc_id = $request->getParameter("id");

        if(!preg_match("/^([A-Z]+)-([A-Z0-9]+)-[0-9]+[0-9\-M]*$/", $doc_id, $matches)) {

            return $this->forward404();
        }

        $doc_type = $matches[1];
        $etablissement = EtablissementClient::getInstance()->find("ETABLISSEMENT-".$matches[2]);

        if(!$etablissement) {

           return $this->forward404();
        }

        if($doc_type == "DREV") {

            return $this->redirect("drev_visualisation", array("id" => $doc_id));
        }

        if($doc_type == "DREVMARC") {

            return $this->redirect("drevmarc_visualisation", array("id" => $doc_id));
        }

        if(in_array($doc_type, array("PARCELLAIREAFFECTATION", "PARCELLAIRECREMANT", "INTENTIONCREMANT"))) {

            return $this->redirect("parcellaire_affectation_visualisation", array("id" => $doc_id));
        }

        if($doc_type == "TIRAGE") {

            return $this->redirect("tirage_visualisation", array("id" => $doc_id));
        }

        if($doc_type == "TRAVAUXMARC") {

            return $this->redirect("travauxmarc_visualisation", array("id" => $doc_id));
        }

        if($doc_type == "PARCELLAIREIRRIGABLE") {

            return $this->redirect("parcellaireirrigable_visualisation", array("id" => $doc_id));
        }

        return $this->forward404();
    }

    public function executeExport(sfWebRequest $request) {
        $this->buildSearch($request);

        $this->setLayout(false);
        $attachement = sprintf("attachment; filename=export_declarations_%s.csv", date('YmdHis'));
        $this->response->setContentType('text/csv');
        $this->response->setHttpHeader('Content-Disposition',$attachement );
    }

    public function executeEtablissementSelection(sfWebRequest $request) {
        $form = new EtablissementChoiceForm('INTERPRO-declaration', array(), true);
        $form->bind($request->getParameter($form->getName()));
        if (!$form->isValid()) {

            return $this->redirect('declaration');
        }

        return $this->redirect('declaration_etablissement', $form->getEtablissement());
    }

    public function executeEtablissement(sfWebRequest $request) {
        $this->etablissement = $this->getRoute()->getEtablissement();

        $this->secureEtablissement($this->etablissement);

        if(class_exists("EtablissementChoiceForm")) {
            $this->form = new EtablissementChoiceForm('INTERPRO-declaration', array('identifiant' => $this->etablissement->identifiant), true);
        }

        $this->campagne = $request->getParameter('campagne', ConfigurationClient::getInstance()->getCampagneManager()->getCurrent());
        if(!$this->getUser()->isAdmin() && $this->campagne != ConfigurationClient::getInstance()->getCampagneManager()->getCurrent()) {

            return $this->forwardSecure();
        }
    }

    public function executeUsurpation(sfWebRequest $request) {
        $this->etablissement = $this->getRoute()->getEtablissement();
        $societe = $this->etablissement->getSociete();

        $this->getUser()->usurpationOn($societe->identifiant, $request->getReferer());
        $this->redirect('declaration_etablissement', array('identifiant' => $societe->getEtablissementPrincipal()->identifiant));
    }

    protected function buildSearch(sfWebRequest $request) {
        $rows = acCouchdbManager::getClient()
                    ->group(true)
                    ->group_level(4)
                    ->getView('declaration', 'tous')->rows;

        $this->facets = array(
            "Type" => array(),
            "Statut" => array(),
            "Campagne" => array(),
            "Mode" => array(),
        );

        $facetToRowKey = array("Type" => DeclarationTousView::KEY_TYPE, "Campagne" => DeclarationTousView::KEY_CAMPAGNE, "Mode" => DeclarationTousView::KEY_MODE, "Statut" => DeclarationTousView::KEY_STATUT);

        $this->query = $request->getParameter('query', array());
        $this->docs = array();

        if(!$this->query || !count($this->query)) {
            $this->docs = acCouchdbManager::getClient()
            ->reduce(false)
            ->getView('declaration', 'tous')->rows;
        }

        foreach($rows as $row) {
            $addition = 0;
            foreach($this->facets as $facetNom => $items) {
                $find = true;
                if($this->query) {
                    foreach($this->query as $queryKey => $queryValue) {
                        if($queryValue != $row->key[$facetToRowKey[$queryKey]]) {
                            $find = false;
                            break;
                        }
                    }
                }
                if(!$find) {
                    continue;
                }
                $facetKey = $facetToRowKey[$facetNom];
                if(!array_key_exists($row->key[$facetKey], $this->facets[$facetNom])) {
                    $this->facets[$facetNom][$row->key[$facetKey]] = 0;
                }
                $this->facets[$facetNom][$row->key[$facetKey]] += $row->value;
                $addition += $row->value;

            }
            if($addition > 0 && $this->query && count($this->query)) {
                $keys = array($row->key[DeclarationTousView::KEY_TYPE], $row->key[DeclarationTousView::KEY_CAMPAGNE], $row->key[DeclarationTousView::KEY_MODE], $row->key[DeclarationTousView::KEY_STATUT]);
                $this->docs = array_merge($this->docs, acCouchdbManager::getClient()
                ->startkey($keys)
                ->endkey(array_merge($keys, array(array())))
                ->reduce(false)
                ->getView('declaration', 'tous')->rows);
            }
        }

        krsort($this->facets["Campagne"]);
        ksort($this->facets["Statut"]);
        ksort($this->facets["Type"]);
        krsort($this->facets["Mode"]);

        uasort($this->docs, function($a, $b) {

            return $a->key[DeclarationTousView::KEY_DATE] < $b->key[DeclarationTousView::KEY_DATE];
        });
    }

    protected function secureEtablissement($etablissement) {
        if (!EtablissementSecurity::getInstance($this->getUser(), $etablissement)->isAuthorized(array())) {

            return $this->forwardSecure();
        }
    }

    protected function forwardSecure() {
        $this->context->getController()->forward(sfConfig::get('sf_secure_module'), sfConfig::get('sf_secure_action'));

        throw new sfStopException();
    }
}
