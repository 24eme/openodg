<?php
class Cotisation
{
	protected $config;
	protected $doc;

	const PRECISION = 2;

	public function __construct($config, $doc)
	{
		$this->config = $config;
		$this->doc = $doc;
	}

	protected function getConfig() {

		return $this->config;
	}

	protected function getConfigCollection() {

		return $this->getConfig()->getParent()->getParent();
	}

	protected function getConfigDocument() {

		return $this->getConfig()->getDocument();
	}

	protected function getConfigPrix() {

		return $this->getConfig()->prix;
	}

	protected function getConfigLibelle() {

		return $this->getConfig()->libelle;
	}

	protected function getConfigComplementLibelle() {

		return $this->getConfig()->complement_libelle;
	}

	protected function getConfigCallback() {

		return $this->getConfig()->callback;
	}

	protected function getDoc() {

		return $this->doc;
	}

	public function getCollectionKey() {

		return $this->getConfigCollection()->getKey();
	}

	public function getDetailKey() {

		return $this->getConfig()->getKey();
	}

	public function getQuantite()
	{

		return 1;
	}

	public function getPrix()
	{

		return round($this->getConfigPrix(), self::PRECISION + 1);
	}

	public function getTotal()
	{

		return round($this->getPrix() * $this->getQuantite(), self::PRECISION);
    }

	public function getLibelle()
	{

		return str_replace('%complement_libelle%', $this->getConfigComplementLibelle(), $this->getConfigLibelle());
	}
}
