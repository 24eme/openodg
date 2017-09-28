<?php
class CotisationVariable extends CotisationFixe
{

	public function getQuantite()
	{
		$quantite = round($this->getCallbackValue(), self::PRECISION);

		return ($quantite >= 0) ? $quantite : 0;
	}

	public function getLibelle()
	{
		return str_replace('%callback%', $this->getCallbackValue(), parent::getLibelle());
	}

	public function getCallbackValue()
	{
		$value = null;
		$callback = $this->callback;
		foreach($this->mouvements as $mouvement) {
			$value += $mouvement->$callback();
		}
		return round($value, self::PRECISION);
	}

}
