<?php

class DRevMention extends BaseDRevMention 
{

	public function getAppellation()
    {
        return $this->getParent();
    }

    public function getChildrenNode() 
    {
        return $this->getLieux();
    }

    public function getLieux()
    {
        return $this->filter('^lieu');
    }
}
