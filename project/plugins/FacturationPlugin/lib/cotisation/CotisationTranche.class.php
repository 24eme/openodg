<?php
class CotisationTranche extends CotisationVariable
{
	protected $tranche;
	
	public function __construct($document, $datas)
	{
		parent::__construct($document, $datas);
		$this->tranche = $datas->tranche;
	}
	
	public function getQuantite()
	{
		return (ceil((round($this->getCallbackValue(), self::PRECISION)) / $this->tranche) - 1);
	}
	
	public function getTotal()
	{
		return round($this->prix * $this->getQuantite(), self::PRECISION);
	}
	
	public function getLibelle()
	{
		return str_replace('%tranche%',$this->tranche, parent::getLibelle());
	}
}