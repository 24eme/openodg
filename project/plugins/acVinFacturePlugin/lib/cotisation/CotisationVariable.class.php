<?php
class CotisationVariable extends CotisationFixe
{

	public function getQuantite()
	{
        $precision = $this->getConfigCallbackParameters()->getParameters('precision') ?: self::PRECISION;
		return round($this->getCallbackValue(), $precision);
	}

	public function getLibelle()
	{
		return str_replace('%callback%', $this->getCallbackValue(), parent::getLibelle());
	}

}
