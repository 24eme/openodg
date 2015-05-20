<?php
class CotisationFixe extends CotisationBase
{
	const SQUEEZE = 'SQUEEZE';
	
	public function getTotal()
	{
		return $this->getPrix();
	}

	
	public function getCallbackValue()
	{
		$document = $this->document;
		$callback = $this->callback;
		if ($this->callback && round($document->$callback(), self::PRECISION) <= 0) {
			return self::SQUEEZE;
		}
		return round($document->$callback(), self::PRECISION);
	}
}