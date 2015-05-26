<?php

class adminComponents extends sfComponents {

    public function executeList(sfWebRequest $request) {

        $this->current_key_list = $request->getParameter('docs', 'DRev 2014');
        $this->statut = $request->getParameter('doc_statut', "a_valider");

        $this->statuts_libelle = array("a_valider" => "À Valider", "valide" => "Validé", "brouillon" => "En cours de saisie");
        $this->lists = array();
        $this->lists["DRev 2014"] = $this->getList("DRev", ConfigurationClient::getInstance()->getCampagneManager()->getCurrent());
        $this->lists["DRev Marc 2014"] = $this->getList("DRevMarc", ConfigurationClient::getInstance()->getCampagneManager()->getCurrent());
        $this->lists["Parcellaire 2015"] = $this->getList("Parcellaire", ConfigurationClient::getInstance()->getCampagneManager()->getCurrentNext(), function($document) { return preg_match("/PARCELLAIRE-/", $document->id); });
        $this->lists["Parcellaire Crémant 2015"] = $this->getList("Parcellaire", ConfigurationClient::getInstance()->getCampagneManager()->getCurrentNext(), function($document) { return preg_match("/PARCELLAIRECREMANT-/", $document->id); });

        if(!($this->statut || $this->statut = "a_valider") && !$this->lists[$this->current_key_list]['statuts']['a_valider']) {
            $this->statut = "valide";
        }
    }

    protected function getList($type, $campagne, $filter = null) {
        $documents = acCouchdbManager::getClient()
                    ->startkey(array($type, $campagne, array()))
                    ->endkey(array($type, $campagne))
                    ->descending(true)
                    ->getView('declaration', 'tous')->rows;

        $lists = array("type" => $type,
                       "campagne" => $campagne,
                       "statuts" => array(
                           "a_valider" => array(), 
                           "valide" => array(), 
                           "brouillon" => array(), 
                       ),
                       "stats" => array(
                            "global" => array("nb_teledeclares" => 0, "nb_papiers" => 0, "nb_can_be_validate" => 0),
                            "a_valider" => array("nb_teledeclares" => 0, "nb_papiers" => 0, "nb_can_be_validate" => 0),
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
            if($document->key[2] && $document->key[7]) {
                $lists["stats"]["global"]["nb_papiers"] += 1;
            }

            if($document->key[2] && !$document->key[7]) {
                $lists["stats"]["global"]["nb_teledeclares"] += 1;
            }

            if ($document->key[2] && !$document->key[3] && !$document->key[6]) {
                $lists["stats"]["global"]["nb_can_be_validate"] += 1;
            }

            if($document->key[3]) {
                $lists["statuts"]["valide"][] = $document;
                
                if($document->key[7]) {
                    $lists["stats"]["valide"]["nb_papiers"] += 1;
                } else {
                    $lists["stats"]["valide"]["nb_teledeclares"] += 1;
                }

                continue;
            }

            if($document->key[2]) {
                $lists["statuts"]["a_valider"][] = $document;

                if($document->key[7]) {
                    $lists["stats"]["a_valider"]["nb_papiers"] += 1;
                } else {
                    $lists["stats"]["a_valider"]["nb_teledeclares"] += 1;
                }

                if(!$document->key[6]) {
                    $lists["stats"]["a_valider"]["nb_can_be_validate"] += 1;
                }

                continue;
            }
            
            $lists["statuts"]["brouillon"][] = $document;

            if($document->key[7]) {
                    $lists["stats"]["brouillon"]["nb_papiers"] += 1;
            } else {
                    $lists["stats"]["brouillon"]["nb_teledeclares"] += 1;
            }
        }

        return $lists;
    }

}
