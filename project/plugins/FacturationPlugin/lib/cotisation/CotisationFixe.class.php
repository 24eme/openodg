<?php
class CotisationFixe extends CotisationBase
{
	public function getTotal()
	{
		return $this->getPrix();
	}
}