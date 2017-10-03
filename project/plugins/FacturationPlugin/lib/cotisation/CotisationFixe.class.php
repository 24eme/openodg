<?php
class CotisationFixe extends Cotisation
{
	public function getQuantite() {
		$callback = $this->getConfigCallback();
		$value = $this->getDoc()->$callback();

		if($callback && round($value, self::PRECISION) <= 0) {

			return 0;
		}

		return parent::getQuantite();
	}
}
