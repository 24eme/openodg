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

		return round(call_user_func_array(array($this->getDoc(), $this->getConfigCallback()), $this->getConfigCallbackParameters()), self::PRECISION);
	}

}
