<?php
/**
 * Model for DRevMouvement
 *
 */

class DRevMouvement extends BaseDRevMouvement {

    public function getSurfaceFacturable()
	{
		return $this->getDocument()->getSurfaceFacturable();
	}

	public function getVolumeFacturable()
	{
		return $this->getDocument()->getVolumeFacturable();
    }

	public function getSurfaceVinifieeFacturable()
	{
		return $this->getDocument()->getSurfaceVinifieeFacturable();
	}

}
