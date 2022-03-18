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
			if($config->isDisabled()) {
				continue;
			}
			foreach ($config->generateCotisations($document) as $cotisation) {
				if($config->exist('fallback') && $config->fallback){
					continue;
				}
                $cle = $cotisation->getHash();
                if ((strpos($cle, '%detail_identifiant%') !== false) && !$document->exist('numero_archive')) {
                    throw new sfException('pas de %detail_identifiant% possible pour la clé '.$cle.' : '.get_class($document)." n'a pas de champs numero_archive");
                }
                if (strpos($cle, '%detail_identifiant%') !== false) {
                    if (!$document->numero_archive) {
                        $document->numero_archive = '';
                    }
                    $cle = str_replace('%detail_identifiant%', $document->numero_archive, $cle);
                }
				$cotisations[$cle] = $cotisation;
			}
		}
		return $cotisations;
	}

	public function getMouvementsFactures($compteIdentifiant, $force = false) {
		$mouvements = array();
		foreach ($this->docs as $docModele) {
			$documents = $this->getDocumentFacturable($docModele, $compteIdentifiant, $this->getCampagne());
			$mouvements = array_merge($mouvements, FactureClient::getInstance()->getMouvementsFacturesByDocs($compteIdentifiant, $documents,$force));
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
