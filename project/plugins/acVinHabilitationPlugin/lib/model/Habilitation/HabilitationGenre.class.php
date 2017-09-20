<?php
/**
 * Model for HabilitationGenre
 *
 */

class HabilitationGenre extends BaseHabilitationGenre {
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
