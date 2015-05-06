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
		return round($this->getCallbackValue(), self::PRECISION);
	}
	
	public function getTotal()
	{
		return round($this->prix * (ceil($this->getQuantite() / $this->tranche) - 1), self::PRECISION);
	}
	
	public function getLibelle()
	{
		return str_replace('%tranche%',$this->tranche, parent::getLibelle());
	}
}