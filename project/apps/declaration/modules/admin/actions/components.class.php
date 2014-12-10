<?php

class adminComponents extends sfComponents {

    public function executeList(sfWebRequest $request) {

        $this->type = $request->getParameter('doc_type', "DRev");
        $this->campagne = $request->getParameter('doc_campagne', "2014");
        $this->statut = $request->getParameter('doc_statut', "a_valider");

        $this->statuts_libelle = array("a_valider" => "À Valider", "brouillon" => "En cours de saisie", "valide" => "Validé");
        $this->lists = array();
        $this->lists["DRev2014"] = $this->getList("DRev", "2014");
        $this->lists["DRevMarc2014"] = $this->getList("DRevMarc", "2014");
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
                           "brouillon" => array(), 
                           "valide" => array(), 
                       ),
                       "stats" => array("nb_teledeclares" => 0, "nb_papiers" => 0, "nb_can_be_validate" => 0));
        foreach($documents as $document) {
            if($document->key[2] && $document->key[7]) {
                $lists["stats"]["nb_papiers"] += 1;
            }

            if($document->key[2] && !$document->key[7]) {
                $lists["stats"]["nb_teledeclares"] += 1;
            }

            if ($document->key[2] && !$document->key[3] && !$document->key[6]) {
                $lists["stats"]["nb_can_be_validate"] += 1;
            }

            if($document->key[3]) {
                $lists["statuts"]["valide"][] = $document;

                continue;
            }

            if($document->key[2]) {
                $lists["statuts"]["a_valider"][] = $document;

                continue;
            }
            
            $lists["statuts"]["brouillon"][] = $document;
        }

        return $lists;
    }

}
