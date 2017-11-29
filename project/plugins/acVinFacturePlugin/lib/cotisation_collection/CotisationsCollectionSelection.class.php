<?php
class CotisationsCollectionSelection extends CotisationsCollection
{

	public function getDetails()
	{
		$details = parent::getDetails();
		$doc = $this->doc;
		$callback = $this->config->callback;
		$selections = $doc->$callback();
		$result = array();
		foreach ($selections as $selection) {
			if ($details->exist($selection)) {
				$result[$selection] = $details->get($selection);
			}
		}
		return $result;
	}

}
