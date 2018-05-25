<?php

class DRevDeclarant extends BaseDRevDeclarant {

    public function getPpm() {
        if(!$this->_get('ppm')) {
            $this->ppm = $this->getDocument()->getEtablissementObject()->ppm;
        }

        return $this->_get('ppm');
    }

    public function getSiret() {
        if(!$this->_get('siret')) {
            $this->siret = $this->getDocument()->getEtablissementObject()->siret;
        }

        return $this->_get('siret');
    }

    public function getTelephone() {
        if($this->exist('telephone') && $this->_get('telephone')) {

            return $this->_get('telephone');
        }

        return ($this->_get('telephone_bureau')) ? $this->_get('telephone_bureau') : $this->_get('telephone_mobile');
    }

}
