<?php

class declarationActions extends sfActions {

    public function executeIndex(sfWebRequest $request) {

        $this->declarations = array();
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

        $facetToRowKey = array("Type" => 0, "Campagne" => 1, "Mode" => 2, "Statut" => 3);

        $this->query = $request->getParameter('query', array("Statut" => "À valider"));
        $this->rows = array();

        if(!$this->query || !count($this->query)) {
            $this->rows = acCouchdbManager::getClient()
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
                $this->rows = array_merge($this->rows, acCouchdbManager::getClient()
                ->startkey(array($row->key[0], $row->key[1], $row->key[2], $row->key[3]))
                ->endkey(array($row->key[0], $row->key[1], $row->key[2], $row->key[3], array()))
                ->reduce(false)
                ->getView('declaration', 'tous')->rows);
            }
        }

        krsort($this->facets["Campagne"]);
        ksort($this->facets["Statut"]);
        ksort($this->facets["Type"]);
        krsort($this->facets["Mode"]);


        uasort($this->rows, function($a, $b) {

            return $a->key[6] < $b->key[6];
        });

        $nbResultatsParPage = 15;
        $this->nbResultats = count($this->rows);
        $this->page = $request->getParameter('page', 1);
        $this->nbPage = ceil($this->nbResultats / $nbResultatsParPage);
        $this->rows = array_slice($this->rows, ($this->page - 1) * $nbResultatsParPage, $nbResultatsParPage);

        $this->form = new LoginForm();

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

        if(!preg_match("/^([A-Z]+)-([0-9]+)-[0-9]+$/", $doc_id, $matches)) {

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

        if(in_array($doc_type, array("PARCELLAIRE", "PARCELLAIRECREMANT"))) {

            return $this->redirect("parcellaire_visualisation", array("id" => $doc_id));
        }

         if($doc_type == "TIRAGE") {

            return $this->redirect("tirage_visualisation", array("id" => $doc_id));
        }

        return $this->forward404();
    }

    public function executeExport(sfWebRequest $request) {
        $current_key_list = $request->getParameter('docs', 'DRev '.ConfigurationClient::getInstance()->getCampagneManager()->getCurrent());
        $statut = $request->getParameter('doc_statut', "a_valider");

        $this->setLayout(false);
        $attachement = sprintf("attachment; filename=export_%s_%s_%s.csv", str_replace("-", "_", strtolower(KeyInflector::slugify($current_key_list))), $statut, date('YmdHis'));
        $this->response->setContentType('text/csv');
        $this->response->setHttpHeader('Content-Disposition',$attachement );
    }

    public function executeEtablissement(sfWebRequest $request) {
        $this->etablissement = $this->getRoute()->getEtablissement();
    }

}
