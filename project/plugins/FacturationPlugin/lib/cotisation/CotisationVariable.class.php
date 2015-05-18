<?php
class CotisationVariable extends CotisationFixe
{
	protected $callback;
	
	public function __construct($template, $document, $datas)
	{
		parent::__construct($template, $document, $datas);
		$this->callback = $datas->callback;
	}
	
	public function getQuantite()
	{
		return round($this->getCallbackValue(), self::PRECISION);
	}
	
	public function getTotal()
	{
		return round($this->prix * $this->getQuantite(), self::PRECISION);
	}
	
	public function getLibelle()
	{
		return str_replace('%callback%', $this->getCallbackValue(), parent::getLibelle());
	}
	
	public function getCallbackValue()
	{
		$document = $this->document;
		$callback = $this->callback;
		return round($document->$callback(), self::PRECISION);
	}
	
}