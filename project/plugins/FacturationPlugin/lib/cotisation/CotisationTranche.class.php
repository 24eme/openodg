<?php
class CotisationTranche extends CotisationVariable
{
	protected $tranche;
	protected $depart;
	
	public function __construct($document, $datas)
	{
		parent::__construct($document, $datas);
		$this->tranche = $datas->tranche;
		$this->depart = $datas->depart;
	}
	
	public function getQuantite()
	{
		return (ceil((round($this->getCallbackValue(), self::PRECISION)) / $this->tranche) - $this->depart);
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