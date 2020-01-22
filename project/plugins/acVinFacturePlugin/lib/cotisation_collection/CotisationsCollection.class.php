<?php
class CotisationsCollection
{
	protected $doc;
	protected $config;

	public function __construct($config, $doc)
	{
		$this->config = $config;
		$this->doc = $doc;
	}

	public function getCotisations() {
		$cotisations = array();
		foreach($this->getDetails() as $detail) {
			$cotisation = $detail->getInstanceCotisation($this->getDoc());

			if(!$cotisation) {
				continue;
			}
			
			$cotisations[] = $cotisation;
		}

		return $cotisations;
	}

	public function getTotal() {
		$total = 0;
		foreach($this->getCotisations() as $cotisation) {
			$total += $cotisation->getTotal();
		}

		return $total;
	}

	protected function getDoc() {

		return $this->doc;
	}

	protected function getDetails()
	{
		return $this->config->details;
	}
}
