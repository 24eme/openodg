<?php
class DRevNoeudRoute extends DRevRoute {

    protected $noeud = null;

    protected function getObjectForParameters($parameters) {
        parent::getObjectForParameters($parameters);
        
        $this->noeud = $this->drev->get("/declaration/certification/genre/" . $parameters["hash"]);
        if (!$this->noeud) {

            throw new sfError404Exception(sprintf('No noeud found for drev id "%s" with key %s.', $parameters['id'], $parameters['hash']));
        }
        return $this->noeud;
    }

    protected function doConvertObjectToArray($object) {  
        $parameters = array_merge(
                        parent::doConvertObjectToArray($object->getDocument()), 
                        array("hash" => str_replace("/declaration/certification/genre/", "", $object->getHash()))
                        );

        return $parameters;
    }

    public function getNoeud() {
        if (!$this->noeud) {
            $this->getObject();
        }
        return $this->noeud;
    }

}