<?php
class CotisationIntervallesFixe extends CotisationVariable
{
	protected function getConfigIntervalles() {

		return $this->getConfig()->intervalles;
	}

	public function getQuantite() {

		return 1;
	}

	public function getPrix()
	{
		$total = 0;
		$quantite = parent::getQuantite();
		foreach ($this->getConfigIntervalles() as $intervalle => $prix) {
			if ($quantite <= $intervalle) {
				if ($variable = $prix->variable) {
					$total = $quantite * $variable;
				} else {
					$total = $prix->prix;
				}

				break;
			}
		}

		return round($total, self::PRECISION);
	}
}
