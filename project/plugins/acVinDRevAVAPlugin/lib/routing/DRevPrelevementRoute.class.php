<?php
class DRevPrelevementRoute extends DRevRoute {

    protected $prelevement = null;

    protected function getObjectForParameters($parameters) {
        parent::getObjectForParameters($parameters);
        
        $this->prelevement = null;

        if($this->drev->prelevements->exist($parameters['prelevement'])) {
            $this->prelevement = $this->drev->prelevements->get($parameters['prelevement']);
        }
       
        if (!$this->prelevement) {

            throw new sfError404Exception(sprintf('No Prelevement found for drev id "%s" with key %s.', $parameters['id'], $parameters['prelevement']));
        }
        return $this->prelevement;
    }

    protected function doConvertObjectToArray($object) {  
        $parameters = array_merge(
                        parent::doConvertObjectToArray($object->getDocument()), 
                        array("prelevement" => $object->getKey())
                        );

        return $parameters;
    }

    public function getPrelevement() {
        if (!$this->prelevement) {
            $this->getObject();
        }
        return $this->prelevement;
    }

}