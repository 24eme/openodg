<?php

class adminActions extends sfActions {
    
    public function executeIndex(sfWebRequest $request) {

        $this->getUser()->signOutEtablissement();
        
        $this->form = new LoginForm();
        
        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

        if(!$this->form->isValid()) {
            
            return sfView::SUCCESS;
        }

        $this->getUser()->signInEtablissement($this->form->getValue('etablissement'));

        return $this->redirect('home'); 
    }

    public function executeDoc(sfWebRequest $request) {
        $this->getUser()->signOutEtablissement();

        $doc_id = $request->getParameter("id");

        if(!preg_match("/^([A-Z]+)-([0-9]+)-[0-9]+$/", $doc_id, $matches)) {
            
            return $this->forward404();
        }

        $doc_type = $matches[1];
        $etablissement = EtablissementClient::getInstance()->find("ETABLISSEMENT-".$matches[2]);

        if(!$etablissement) {

           return $this->forward404(); 
        }

        $this->getUser()->signInEtablissement($etablissement);

        if($doc_type == "DREV") {

            return $this->redirect("drev_visualisation", array("id" => $doc_id, "service" => $request->getParameter("service")));
        }

        if($doc_type == "DREVMARC") {

            return $this->redirect("drevmarc_visualisation", array("id" => $doc_id, "service" => $request->getParameter("service")));
        }

        if(in_array($doc_type, array("PARCELLAIRE", "PARCELLAIRECREMANT"))) {

            return $this->redirect("parcellaire_visualisation", array("id" => $doc_id, "service" => $request->getParameter("service")));
        }

        return $this->forward404();
    }

    public function executeExport(sfWebRequest $request) {
        $current_key_list = $request->getParameter('docs', 'DRev 2014');
        $statut = $request->getParameter('doc_statut', "a_valider");

        $this->setLayout(false);
        $attachement = sprintf("attachment; filename=export_%s_%s_%s.csv", str_replace("-", "_", strtolower(KeyInflector::slugify($current_key_list))), $statut, date('YmdHis'));
        $this->response->setContentType('text/csv');
        $this->response->setHttpHeader('Content-Disposition',$attachement );
    }

}
