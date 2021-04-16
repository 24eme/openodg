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

	public function getConfigCollection() {

		return $this->getConfig()->getParent()->getParent();
	}

	protected function getConfigDocument() {

		return $this->getConfig()->getDocument();
	}

	protected function getConfigPrix() {

		return $this->getConfig()->prix;
	}

	protected function getConfigTva() {

		return $this->getConfig()->tva;
	}

	public function getConfigLibelle() {

		return $this->getConfig()->libelle;
	}

	protected function getConfigComplementLibelle() {

		return $this->getConfig()->complement_libelle;
	}

	public function getConfigCallback() {

		return $this->getConfig()->callback;
	}

	public function getConfigCallbackParameters() {
		if(!$this->getConfig()->exist('callback_parameters')) {
			return array();
		}
		return $this->getConfig()->callback_parameters->toArray(true, false);
	}


	protected function getDoc() {

		return $this->doc;
	}

	public function getHash() {

		return $this->getConfig()->getHash();
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

	public function getTva()
	{

		return round($this->getConfigTva(), self::PRECISION + 1);
	}

	public function getTotal()
	{

		return round($this->getPrix() * $this->getQuantite(), self::PRECISION);
    }

	public function getLibelle()
	{

		return str_replace('%complement_libelle%', $this->getConfigComplementLibelle(), $this->getConfigLibelle());
	}

	public function getUnite() {
		if(!$this->getConfig()->exist('unite')) {
			return null;
		}

		return $this->getConfig()->unite;
	}
}
