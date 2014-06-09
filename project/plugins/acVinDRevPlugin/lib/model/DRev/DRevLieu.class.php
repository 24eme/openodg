<?php

class DRevLieu extends BaseDRevLieu 
{


	public function getMention() 
    {
        return $this->getParent();
    }

    public function getAppellation() 
    {
        return $this->getMention()->getParent();
    }

    public function getChildrenNode() 
    {
        return $this->getCouleurs();
    }

    public function getCouleurs() 
    {
        return $this->filter('^couleur');
    }

}
