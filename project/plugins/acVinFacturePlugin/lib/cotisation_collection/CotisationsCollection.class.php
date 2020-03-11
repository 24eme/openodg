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
		$total = 0;
		foreach($this->getDetails() as $detail) {
			$cotisation = $detail->getInstanceCotisation($this->getDoc());

			if(!$cotisation) {
				continue;
			}

			$total += $cotisation->getTotal();
			$cotisations[] = $cotisation;
		}
		if($this->config->exist('minimum') && ($minimum = $this->config->minimum)
      && $this->config->exist('minimum_fallback') && ($minimum_fallback_name = $this->config->minimum_fallback)
			&& $this->config->getDocument()->cotisations->exist($minimum_fallback_name) && ($minimum_fallback = $this->config->getDocument()->cotisations->$minimum_fallback_name)
			&& ($total <= $minimum) && ($total > 0) && (! $this->getDoc() instanceof DRev)){
				return $minimum_fallback->generateCotisations($this->getDoc());
		}
		
		//A confer pour l'ava pour garder les lignes à 0
		if(!$total){
			return null;
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
