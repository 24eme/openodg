<?php
class Cotisation
{
	protected $document;
	protected $prix;
	protected $tva;
	protected $libelle;
	protected $complementLibelle;
	
	const PRECISION = 2;
	
	public function __construct($document, $datas)
	{
		$this->document = $document;
		$this->prix = $datas->prix;
		$this->tva = $datas->tva;
		$this->libelle = $datas->libelle;
		$this->complementLibelle = (isset($datas->complement_libelle))? $datas->complement_libelle : null;
	}
	
	public function getQuantite()
	{
		return 1;
	}
	
	public function getPrix()
	{
		return round($this->prix, self::PRECISION);
	}
	
	public function getTva()
	{
		return ($this->tva)? round($this->tva * $this->getTotal(), self::PRECISION) : 0;
	}
	
	public function getLibelle()
	{
		return str_replace('%complement_libelle%', $this->complementLibelle, $this->libelle);
	}
	
	public function getTotal() {
		return null;
	}
	
}