<?php
class HabilitationRoute extends EtablissementRoute implements InterfaceHabilitationRoute {

    protected $habilitation = null;

    protected function getObjectForParameters($parameters = null) {

        $this->habilitation = HabilitationClient::getInstance()->find($parameters['id']);
        if (!$this->habilitation) {

            throw new sfError404Exception(sprintf('No Habilitation found with the id "%s".', $parameters['id']));
        }
        return $this->habilitation;
    }

    protected function doConvertObjectToArray($object = null) {
        $parameters = array("id" => $object->_id);
        return $parameters;
    }

    public function getHabilitation() {
        if (!$this->habilitation) {
            $this->getObject();
        }
        return $this->habilitation;
    }

    public function getEtablissement() {

        return $this->getHabilitation()->getEtablissementObject();
    }

}
