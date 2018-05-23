<?php
class CotisationCotisationFixe extends CotisationVariable
{
	protected function getConfigMinimum() {

		return round($this->getConfig()->minimum, self::PRECISION);
	}

	protected function getConfigComplement() {

		return round($this->getConfig()->complement, self::PRECISION);
	}

	public function getQuantite() {

		return 1;
	}

	public function getPrix()
	{
		$total = round($this->getConfigPrix() * parent::getQuantite(), self::PRECISION);
		$total = ($this->getConfigMinimum() && $this->getConfigMinimum() > $total) ? $this->getConfigMinimum() : $total;

		return round($total + $this->getConfigComplement(), self::PRECISION);
	}

	public function getCallbackValue()
	{
		$hash = $this->getConfig()->callback;
		$value = 0;
		if ($this->getConfigDocument()->exist($hash)) {
			$value = $this->getConfigDocument()->get($hash)->getInstanceCotisation($this->getDoc())->getTotal();
		}


		return round($value, self::PRECISION);
	}

}
