<?php
class CotisationTranche extends CotisationVariable
{

	protected function getConfigDepart() {

		return $this->getConfig()->depart;
	}

	protected function getConfigTranche() {

		return $this->getConfig()->tranche;
	}

	public function getQuantite()
	{
		$quantite = (ceil((round($this->getCallbackValue(), self::PRECISION)) / $this->getConfigTranche()) - $this->getConfigDepart());

		return ($quantite >= 0)? $quantite : 0;
	}

	public function getLibelle()
	{
		return str_replace('%tranche%', $this->getConfigTranche(), parent::getLibelle());
	}
}
