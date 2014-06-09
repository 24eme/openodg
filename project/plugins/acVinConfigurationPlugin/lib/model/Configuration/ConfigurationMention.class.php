<?php

class ConfigurationMention extends BaseConfigurationMention {

    public function hasManyLieu() {

        return $this->hasManyNoeuds();
    }

    public function getChildrenNode() {

        return $this->getLieux();
    }
    
	public function getAppellation() {

        return $this->getParentNode();
    }

    public function getLieux(){
        return $this->filter('^lieu');
    }
}
