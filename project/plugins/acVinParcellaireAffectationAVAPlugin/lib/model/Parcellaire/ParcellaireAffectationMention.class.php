<?php
/**
 * Model for ParcellaireMention
 *
 */

class ParcellaireAffectationMention extends BaseParcellaireAffectationMention {

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

    public function hasLieux() {
        return $this->getLieux();
    }
}