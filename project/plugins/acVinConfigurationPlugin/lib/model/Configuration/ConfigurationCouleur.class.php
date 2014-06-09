<?php

class ConfigurationCouleur extends BaseConfigurationCouleur {

    public function getLieu() {

        return $this->getParentNode();
    }
    
	public function getMention() {

        return $this->getLieu()->getMention();
    }
    
	public function getAppellation() {

        return $this->getMention()->getAppellation();
    }

    public function getCepages() {
      return $this->filter('^cepage');
    }

    public function getChildrenNode() {

        return $this->getCepages();
    }

    public function getRendementNoeud() {

        return $this->getRendementCouleur();
    }
    
    public function getDrevProduits()
    {
    	if ($this->exist('drev') && $this->get('drev')) {
    		return array($this->getHash());
    	}
    	return array();
    }
}
