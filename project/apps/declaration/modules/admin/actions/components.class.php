<?php

class adminComponents extends sfComponents {

    public function executeList(sfWebRequest $request) {

        $this->type = $request->getParameter('doc_type', "DRev");
        $this->campagne = $request->getParameter('doc_campagne', "2014");
        $this->statut = $request->getParameter('doc_statut', "a_valider");

        $this->statuts_libelle = array("a_valider" => "À Valider", "valide" => "Validé", "brouillon" => "En cours de saisie");
        $this->lists = array();
        $this->lists["DRev2014"] = $this->getList("DRev", ConfigurationClient::getInstance()->getCampagneManager()->getCurrent());
        $this->lists["DRevMarc2014"] = $this->getList("DRevMarc", ConfigurationClient::getInstance()->getCampagneManager()->getCurrent());
        $this->lists["Parcellaire2015"] = $this->getList("Parcellaire", ConfigurationClient::getInstance()->getCampagneManager()->getCurrentNext());
    }

    protected function getList($type, $campagne) {
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
