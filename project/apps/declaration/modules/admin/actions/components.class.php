<?php

class adminComponents extends sfComponents {

    public function executeList(sfWebRequest $request) {

        $this->current_key_list = $request->getParameter('docs', 'DRev '.ConfigurationClient::getInstance()->getCampagneManager()->getCurrent());
        $this->statut = $request->getParameter('doc_statut');

        $this->statuts_libelle = array("a_valider" => "À Valider", "valide" => "Validé", "brouillon" => "En cours de saisie");
        
        $this->buildLists();

        if(!$this->statut && !$this->lists[$this->current_key_list]['statuts']['a_valider']) {
            $this->statut = "valide";
        } elseif(!$this->statut) {
            $this->statut = "a_valider";
        }
    }

    public function executeExport(sfWebRequest $request) {
        $this->current_key_list = $request->getParameter('docs', 'DRev '.ConfigurationClient::getInstance()->getCampagneManager()->getCurrent());
        $this->statut = $request->getParameter('doc_statut', "a_valider");
        $this->buildLists();
    }

    protected function buildLists() {
        $this->lists = array();
        $this->lists["DRev ".ConfigurationClient::getInstance()->getCampagneManager()->getCurrent()] = $this->getList("DRev", ConfigurationClient::getInstance()->getCampagneManager()->getCurrent());
        $this->lists["DRev Marc ".ConfigurationClient::getInstance()->getCampagneManager()->getCurrent()] = $this->getList("DRevMarc", ConfigurationClient::getInstance()->getCampagneManager()->getCurrent());
        $this->lists["Parcellaire ".ConfigurationClient::getInstance()->getCampagneManager()->getCurrentNext()] = $this->getList("Parcellaire", ConfigurationClient::getInstance()->getCampagneManager()->getCurrentNext(), function($document) { return preg_match("/PARCELLAIRE-/", $document->id); });
        $this->lists["Parcellaire Crémant ".ConfigurationClient::getInstance()->getCampagneManager()->getCurrentNext()] = $this->getList("Parcellaire", ConfigurationClient::getInstance()->getCampagneManager()->getCurrentNext(), function($document) { return preg_match("/PARCELLAIRECREMANT-/", $document->id); });
        $this->lists["Tirage ".ConfigurationClient::getInstance()->getCampagneManager()->getCurrent()] = $this->getList("Tirage", ConfigurationClient::getInstance()->getCampagneManager()->getCurrent(), function($document) { return preg_match("/TIRAGE-/", $document->id); });
    }

    protected function getList($type, $campagne, $filter = null) {
        $documents = acCouchdbManager::getClient()
                    ->startkey(array($type, $campagne, array()))
                    ->endkey(array($type, $campagne))
                    ->descending(true)
                    ->reduce(false)
                    ->getView('declaration', 'tous')->rows;

        $lists = array("type" => $type,
                       "campagne" => $campagne,
                       "statuts" => array(
                           "a_valider" => array(), 
                           "valide" => array(), 
                           "brouillon" => array(), 
                       ),
                       "stats" => array(
                            "global" => array("nb_teledeclares" => 0, "nb_papiers" => 0, "nb_can_be_validate" => 0, "nb_brouillon" => 0),
                            "a_valider" => array("nb_teledeclares" => 0, "nb_papiers" => 0, "nb_can_be_validate" => 0, ""),
                            "valide" => array("nb_teledeclares" => 0, "nb_papiers" => 0, "nb_can_be_validate" => 0),
                            "brouillon" => array("nb_teledeclares" => 0, "nb_papiers" => 0, "nb_can_be_validate" => 0),
                        )
                    );

        foreach ($documents as $key => $document) {
            if(!$filter) {
                continue;
            }

            if(!$filter($document)) {

                unset($documents[$key]);
            }
        }

        foreach($documents as $document) {
            if($document->key[DeclarationTousView::KEY_AUTOMATIQUE] && $document->key[DeclarationTousView::KEY_AUTOMATIQUE]) {
                
                continue;
            }

            if($document->key[DeclarationTousView::KEY_VALIDATION] && $document->key[DeclarationTousView::KEY_PAPIER]) {
                $lists["stats"]["global"]["nb_papiers"] += 1;
            }

            if($document->key[DeclarationTousView::KEY_VALIDATION] && !$document->key[DeclarationTousView::KEY_PAPIER]) {
                $lists["stats"]["global"]["nb_teledeclares"] += 1;
            }

            if ($document->key[DeclarationTousView::KEY_VALIDATION] && !$document->key[DeclarationTousView::KEY_VALIDATION_ODG] && !$document->key[DeclarationTousView::KEY_NB_DOC_EN_ATTENTE]) {
                $lists["stats"]["global"]["nb_can_be_validate"] += 1;
            }

             if (!$document->key[DeclarationTousView::KEY_VALIDATION] && !$document->key[DeclarationTousView::KEY_VALIDATION_ODG]) {
                $lists["stats"]["global"]["nb_brouillon"] += 1;
            }

            if($document->key[DeclarationTousView::KEY_VALIDATION_ODG]) {
                $lists["statuts"]["valide"][] = $document;
                
                if($document->key[DeclarationTousView::KEY_PAPIER]) {
                    $lists["stats"]["valide"]["nb_papiers"] += 1;
                } else {
                    $lists["stats"]["valide"]["nb_teledeclares"] += 1;
                }

                continue;
            }

            if($document->key[DeclarationTousView::KEY_VALIDATION]) {
                $lists["statuts"]["a_valider"][] = $document;

                if($document->key[DeclarationTousView::KEY_PAPIER]) {
                    $lists["stats"]["a_valider"]["nb_papiers"] += 1;
                } else {
                    $lists["stats"]["a_valider"]["nb_teledeclares"] += 1;
                }

                if(!$document->key[DeclarationTousView::KEY_NB_DOC_EN_ATTENTE]) {
                    $lists["stats"]["a_valider"]["nb_can_be_validate"] += 1;
                }

                continue;
            }
            
            $lists["statuts"]["brouillon"][] = $document;

            if($document->key[DeclarationTousView::KEY_PAPIER]) {
                    $lists["stats"]["brouillon"]["nb_papiers"] += 1;
            } else {
                    $lists["stats"]["brouillon"]["nb_teledeclares"] += 1;
            }
        }

        return $lists;
    }

}
