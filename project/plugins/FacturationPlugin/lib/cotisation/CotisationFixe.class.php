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
		return ($this->callback)? round($document->$callback(), self::PRECISION) : self::SQUEEZE;
	}
}