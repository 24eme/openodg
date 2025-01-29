<?php

class DRRoute extends EtablissementRoute implements InterfaceDeclarationRoute
{
    protected $dr = null;

    protected function getObjectForParameters($parameters = null)
    {
        $this->dr = DRClient::getInstance()->find($parameters['id']);
        if (!$this->dr && strpos($parameters['id'], 'DRBAILLEUR') !== false) {
            $this->dr = DRClient::getInstance()->createDoc(explode("-", $parameters['id'])[1], explode("-", $parameters['id'])[2]);
            $this->dr->add('has_metayers', true);
        }
        if(!$this->dr) {
            throw new sfError404Exception(sprintf('No DR found with the id "%s".', $parameters['id']));
        }
        parent::getObjectForParameters(['identifiant' => $this->dr->identifiant]);
        return $this->dr;
    }

    protected function doConvertObjectToArray($object = null)
    {
        $parameters = ["id" => $object->_id];
        return $parameters;
    }

    public function getDR()
    {
        if (! $this->dr) {
            $this->getObject();
        }
        return $this->dr;
    }
}

