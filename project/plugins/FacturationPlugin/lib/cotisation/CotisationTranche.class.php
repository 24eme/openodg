<?php
class CotisationTranche extends CotisationVariable
{
	protected $tranche;
	protected $depart;
	protected $complement;
	
	public function __construct($template, $document, $datas)
	{
		parent::__construct($template, $document, $datas);
		$this->tranche = $datas->tranche;
		$this->depart = $datas->depart;
		$this->complement = round($datas->complement, self::PRECISION);
	}
	
	public function getQuantite()
	{
		return (ceil((round($this->getCallbackValue(), self::PRECISION)) / $this->tranche) - $this->depart);
	}
	
	public function getTotal()
	{
		return round(($this->prix * $this->getQuantite()) + $this->complement, self::PRECISION);
	}
	
	public function getLibelle()
	{
		return str_replace('%tranche%',$this->tranche, parent::getLibelle());
	}
}