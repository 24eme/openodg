<?php

class DRevAppellation extends BaseDRevAppellation 
{

	public function getGenre()
    {
        return $this->getParent();
    }
    
    public function getChildrenNode() 
    {
        return $this->getMentions();
    }

    public function getMentions()
    {
        return $this->filter('^mention');
    }

    public function getLieux() 
    {  
        return $this->mention->getLieux();
    }
    
    public function getLibelleComplet() 
    {
        return $this->libelle;
    }

}
