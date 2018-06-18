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
			if(!$config->isForType($document->getType())) {
				continue;
			}
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
			$mouvements = array_merge($mouvements, FactureClient::getInstance()->getMouvementsByDocs($compteIdentifiant, $documents));
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
