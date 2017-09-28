<?php
/**
 * Model for DRevMouvement
 *
 */

class DRevMouvement extends BaseDRevMouvement {

    public function getSurfaceFacturable()
	{
		return $this->getDocument()->declaration->getTotalTotalSuperficie();
	}

	public function getVolumeFacturable()
	{
		return $this->getDocument()->declaration->getTotalVolumeRevendique();
	}

	public function getSurfaceVinifieeFacturable()
	{
		return $this->getDocument()->declaration->getTotalSuperficieVinifiee();
	}

}
