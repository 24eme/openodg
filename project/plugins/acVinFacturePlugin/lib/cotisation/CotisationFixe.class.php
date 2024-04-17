<?php
class CotisationFixe extends Cotisation
{
	public function getQuantite() {
		$callback = $this->getConfigCallback();

        if($callback && round($this->getCallbackValue(), self::PRECISION) <= 0) {

            return 0;
        }

		return parent::getQuantite();
	}

    public function getCallbackValue()
	{
        $precision = $this->getConfigCallbackParameters()->getParameters('precision') ?: self::PRECISION;

        return round(
            call_user_func([$this->getDoc(), $this->getConfigCallback()], $this->getConfigCallbackParameters()),
            $precision
        );
	}
}
