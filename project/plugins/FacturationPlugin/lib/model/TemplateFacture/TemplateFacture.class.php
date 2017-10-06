<?php
/**
 * Model for TemplateFacture
 *
 */

class TemplateFacture extends BaseTemplateFacture
{

	public function generateCotisations($document)
	{
		$cotisations = array();
		foreach ($this->cotisations as $config) {
			foreach ($config->generateCotisations($document) as $cotisation) {
				$cotisations[$cotisation->getHash()] = $cotisation;
			}
		}

		return $cotisations;
	}

	public function getMouvements($compteIdentifiant, $force = false) {
		$mouvements = array();
		foreach ($this->docs as $docModele) {
			$documents = $this->getDocumentFacturable($docModele, $compteIdentifiant, $this->getCampagne());

			foreach($documents as $doc) {
				if(!count($doc->mouvements)) {
					$doc->generateMouvements();
					$doc->save();
				}

				if(!$doc->exist('mouvements/'.$compteIdentifiant)) {
					continue;
				}

				$mouvs = $doc->mouvements->get($compteIdentifiant);

				foreach($mouvs as $m) {
					if((!$m->isFacturable() || $m->facture) && !$force) {

						continue;
					}

					$mouvements[] = $m;
				}
			}
		}

		return $mouvements;
	}

	public function getDocumentFacturable($docModele, $identifiant, $campagne)
	{
		$client = acCouchdbManager::getClient($docModele);
		if ($client instanceof FacturableClient) {

			return $client->findFacturable($identifiant, $campagne);
		}
		throw new sfException($docModele.'Client must implements FacturableClient interface');
	}

}
