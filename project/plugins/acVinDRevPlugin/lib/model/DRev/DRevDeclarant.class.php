<?php

class DRevDeclarant extends BaseDRevDeclarant {

    public function getPpm() {
        if(!$this->_get('ppm')) {
            return $this->getDocument()->getEtablissementObject()->ppm;
        }

        return $this->_get('ppm');
    }

}
