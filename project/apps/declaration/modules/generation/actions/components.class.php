<?php

class generationComponents extends sfComponents {

    public function executeView(sfWebRequest $request) {

        $this->current_key_list = $request->getParameter('docs', 'DRev 2014');
        $this->statut = $request->getParameter('doc_statut');

        $this->statuts_libelle = array("a_valider" => "À Valider", "valide" => "Validé", "brouillon" => "En cours de saisie");
        
        $this->buildLists();

        if(!$this->statut && !$this->lists[$this->current_key_list]['statuts']['a_valider']) {
            $this->statut = "valide";
        } elseif(!$this->statut) {
            $this->statut = "a_valider";
        }
    }

}
