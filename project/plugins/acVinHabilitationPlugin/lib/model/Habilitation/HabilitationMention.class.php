<?php
/**
 * Model for HabilitationMention
 *
 */

class HabilitationMention extends BaseHabilitationMention {

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
