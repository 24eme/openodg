<?php

class adminComponents extends sfComponents {

    public function executeList(sfWebRequest $request) {

        $this->type = $request->getParameter('doc_type', "DRev");
        $this->campagne = $request->getParameter('doc_campagne', "2014");
        $this->statut = $request->getParameter('doc_statut', "a_valider");

        $this->statuts_libelle = array("a_valider" => "À Valider", "brouillon" => "En cours de saisie", "valide" => "Validé");

        $this->documents = acCouchdbManager::getClient()
                    ->startkey(array($this->type, $this->campagne, array()))
                    ->endkey(array($this->type, $this->campagne))
                    ->descending(true)
                    ->getView('declaration', 'tous')->rows;

        $this->lists = array("a_valider" => array(), "brouillon" => array(), "valide" => array());
        $this->nb_teledeclares = 0;
        $this->nb_papiers = 0;
        foreach($this->documents as $document) {
            if($document->key[2] && $document->key[7]) {
                $this->nb_papiers += 1;
            }

            if($document->key[2] && !$document->key[7]) {
                $this->nb_teledeclares += 1;
            }

            if($document->key[3]) {
                $this->lists["valide"][] = $document;

                continue;
            }

            if($document->key[2]) {
                $this->lists["a_valider"][] = $document;

                continue;
            }
            
            $this->lists["brouillon"][] = $document;
        }
    }

}
