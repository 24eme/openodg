<?php
class TourneeRoute extends sfObjectRoute {

    protected $tournee = null;

    protected function getObjectForParameters($parameters) {

        $this->tournee = TourneeClient::getInstance()->find($parameters['id']);
        if (!$this->tournee) {

            throw new sfError404Exception(sprintf('No Degustation found with the id "%s".', $parameters['id']));
        }
        return $this->tournee;
    }

    protected function doConvertObjectToArray($object) {  
        $parameters = array("id" => $object->_id);
        return $parameters;
    }

    public function getTournee() {
        if (!$this->tournee) {
            $this->getObject();
        }
        return $this->tournee;
    }

}