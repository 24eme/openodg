<?php

class ConfigurationMention extends BaseConfigurationMention {

    public function hasManyLieu() {

        return $this->hasManyNoeuds();
    }

    public function getChildrenNode() {

        return $this->getLieux();
    }

    public function getLieux(){
        return $this->filter('^lieu');
    }
}
