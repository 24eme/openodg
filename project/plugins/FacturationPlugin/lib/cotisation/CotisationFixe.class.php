<?php
class CotisationFixe extends CotisationBase
{
	public function getTotal()
	{
		return round($this->prix * $this->getQuantite(), self::PRECISION);
	}

	public function getQuantite() {
		$value = null;
		$callback = $this->callback;
		foreach($this->mouvements as $mouvement) {
			$value += $mouvement->$callback();
		}

		if($callback && round($value, self::PRECISION) <= 0) {

			return 0;
		}

		return parent::getQuantite();
	}
}
