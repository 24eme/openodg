<?php
class CotisationFixe extends Cotisation
{
	public function getQuantite() {
		$callback = $this->getConfigCallback();
		
		if($callback && round($this->getDoc()->$callback(), self::PRECISION) <= 0) {

			return 0;
		}

		return parent::getQuantite();
	}
}
