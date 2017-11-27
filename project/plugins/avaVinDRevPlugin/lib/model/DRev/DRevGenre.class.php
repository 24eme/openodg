<?php

class DRevGenre extends BaseDRevGenre 
{

	public function getCertification() 
    {
        return $this->getParent();
    }

    public function getChildrenNode()
    {
        return $this->getAppellations();
    }

    public function getAppellations() 
    {
        return $this->filter('^appellation');
    }
}
