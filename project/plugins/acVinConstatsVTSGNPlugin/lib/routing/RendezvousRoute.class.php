<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of RendezvousRouting
 *
 * @author mathurin
 */
class RendezvousRoute extends sfObjectRoute {

    protected $rendezvous = null;

    protected function getObjectForParameters($parameters) {

        $this->rendezvous = RendezvousClient::getInstance()->find($parameters['id']);
        if (!$this->rendezvous) {

            throw new sfError404Exception(sprintf('No rendezvous found with the identifiant "%s" and the dateheure "%s".', $parameters['identifiant'], $parameters['dateheure']));
        }
        return $this->rendezvous;
    }

    protected function doConvertObjectToArray($object) {  
        $parameters = array("id" => $object->_id);
        return $parameters;
    }

    public function getRendezvous() {
        if (!$this->rendezvous) {
            $this->getObject();
        }
        return $this->rendezvous;
    }

}