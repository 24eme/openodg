<?php
class CotisationFixe extends Cotisation
{
	public function getTotal()
	{
		return $this->getPrix();
	}
}