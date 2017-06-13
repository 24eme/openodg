<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ConstatsRoute
 *
 * @author mathurin
 */
class ConstatsRoute extends sfObjectRoute implements InterfaceConstatsRoute {

    protected $constat = null;

    protected function getObjectForParameters($parameters) {

        $this->constat = ConstatsClient::getInstance()->findByIdentifiantAndCampagne($parameters['identifiant'],$parameters['campagne']);
        if (!$this->constat) {

            throw new sfError404Exception(sprintf('No constat found with the identifiant "%s" campagne "%s".', $parameters['identifiant'],$parameters['campagne']));
        }
        return $this->constat;
    }

    protected function doConvertObjectToArray($object) {
        $parameters = array("identifiant" => $object->cvi, "campagne" => $object->campagne);
        return $parameters;
    }

    public function getConstats() {
        if (!$this->constat) {
            $this->getObject();
        }
        return $this->constat;
    }

}
