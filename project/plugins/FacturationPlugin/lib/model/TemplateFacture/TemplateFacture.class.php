<?php
/**
 * Model for TemplateFacture
 *
 */

class TemplateFacture extends BaseTemplateFacture 
{
	
	public function generateCotisations($identifiant, $campagne)
	{
		$template = $this;
		$cotisations = array();
		foreach ($this->docs as $doc) {
			$document = $this->getDocumentFacturable($doc, $identifiant, $campagne);
			if(!$document) {

				throw new sfException(sprintf("Le document %s du compte %s n'a pas été trouvé (%s-%s-%s)", strtoupper($doc), $identifiant, strtoupper($doc), $identifiant, $campagne));
			}

			if(!count($document->mouvements)) {
				$document->generateMouvements();
				$document->save();
			}

			if($document->isFactures()) {
				//continue;
			}

			foreach ($this->cotisations as $key => $cotisation) {
				
				$modele = $cotisation->modele;
				$object = new $modele(CompteClient::getInstance()->findByIdentifiant('E'.$identifiant), $cotisation->callback);
				$details = $object->getDetails($cotisation->details);
				
				if (!in_array($cotisation->libelle, array_keys($cotisations))) {
					$cotisations[$key] = array();
					$cotisations[$key]["libelle"] = $cotisation->libelle;
					$cotisations[$key]["details"] = array();
					$cotisations[$key]["origines"] = array();
				}
				foreach ($details as $type => $detail) {
					$docs = $detail->docs->toArray();
					if (in_array($document->type, $docs)) {
						$modele = $detail->modele;
						$object = new $modele($template, $document, $detail);
						if ($object->getCallbackValue() === CotisationFixe::SQUEEZE) {
							continue;
						}
						if ($key == 'syndicat_viticole') {
							$cotisations[$key]["details"][] = array("libelle" => $object->getLibelle(), "taux" => $detail->tva, "prix" => $object->getTotal(), "total" => $object->getTotal(), "tva" => $object->getTva(), "quantite" => 1);
						} else {
							$cotisations[$key]["details"][] = array("libelle" => $object->getLibelle(), "taux" => $detail->tva, "prix" => $object->getPrix(), "total" => $object->getTotal(), "tva" => $object->getTva(), "quantite" => $object->getQuantite());
						}
						$cotisations[$key]["origines"][$document->_id] = array($this->_id);
					}
				}
			}
		}
		return $cotisations;
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