<?php
/**
 * Model for TemplateFactureCotisation
 *
 */

class TemplateFactureCotisation extends BaseTemplateFactureCotisation {

	public function getTotal($mouvements)
	{
		$total = 0;
		foreach ($this->details as $type => $detail) {
			$total += $detail->getTotal($mouvements);
		}
		return $total;
	}
}
