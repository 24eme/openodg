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

	public function isConfigRequired() {

		return $this->config->isRequired();
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

        if($this->config->exist('minimum') && $this->config->exist('minimum_fallback')) {
            $minimum = $this->config->minimum;
            $minimum_fallback_name = $this->config->minimum_fallback;

            if ($this->config->getDocument()->cotisations->exist($minimum_fallback_name)) {
                $minimum_fallback = $this->config->getDocument()->cotisations->$minimum_fallback_name;

                if ($total <= $minimum && $total > 0 && $minimum_fallback->isForType($this->getDoc()->getType())) {
                    return $minimum_fallback->generateCotisations($this->getDoc());
                }
            }
        }

		if(!$total && !$this->isConfigRequired()) {
			return array();
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
