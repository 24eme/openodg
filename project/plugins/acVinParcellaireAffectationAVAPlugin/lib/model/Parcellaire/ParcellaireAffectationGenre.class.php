<?php
/**
 * Model for ParcellaireGenre
 *
 */

class ParcellaireAffectationGenre extends BaseParcellaireAffectationGenre {

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