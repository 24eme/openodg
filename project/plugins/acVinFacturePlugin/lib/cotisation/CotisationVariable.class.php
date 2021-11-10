<?php
class CotisationVariable extends CotisationFixe
{

	public function getQuantite()
	{
		return round($this->getCallbackValue(), self::PRECISION);
	}

	public function getLibelle()
	{
		return str_replace('%callback%', $this->getCallbackValue(), parent::getLibelle());
	}

}
