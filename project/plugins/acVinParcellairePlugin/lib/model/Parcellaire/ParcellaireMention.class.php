<?php
/**
 * Model for ParcellaireMention
 *
 */

class ParcellaireMention extends BaseParcellaireMention {

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