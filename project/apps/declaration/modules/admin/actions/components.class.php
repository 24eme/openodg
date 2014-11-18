<?php

class adminComponents extends sfComponents {

    public function executeList(sfWebRequest $request) {

        $this->type = $request->getParameter('doc_type', "DRev");
        $this->campagne = $request->getParameter('doc_campagne', "2014");
        $this->statut = $request->getParameter('doc_statut', "a_valider");

        $this->statuts_libelle = array("a_valider" => "À Valider", "brouillon" => "En cours de saisie", "valide" => "Validé");

        $this->documents = acCouchdbManager::getClient()->startkey(array($this->type, $this->campagne))
                    ->endkey(array($this->type, $this->campagne, array()))
                    ->getView('declaration', 'tous')->rows;

        $this->lists = array("a_valider" => array(), "brouillon" => array(), "valide" => array());
        foreach($this->documents as $document) {
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
