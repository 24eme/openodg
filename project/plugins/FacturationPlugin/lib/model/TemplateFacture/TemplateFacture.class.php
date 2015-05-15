<?php
/**
 * Model for TemplateFacture
 *
 */

class TemplateFacture extends BaseTemplateFacture 
{
	
	public function generateCotisations($identifiant, $campagne)
	{
		$cotisations = array();
		foreach ($this->docs as $doc) {
			$document = $this->getDocumentFacturable($doc, $identifiant, $campagne);
			if(!$document) {
				throw new sfException(sprintf("Document not find %s-%s-%s", strtoupper($doc), $identifiant, $campagne));
			}
			foreach ($this->cotisations as $key => $cotisation) {
				if (!in_array($cotisation->libelle, array_keys($cotisations))) {
					$cotisations[$key] = array();
					$cotisations[$key]["libelle"] = $cotisation->libelle;
					$cotisations[$key]["details"] = array();
				}
				foreach ($cotisation->details as $type => $detail) {
					$docs = $detail->docs->toArray();
					if (in_array($document->type, $docs)) {
						$modele = $detail->modele;
						$object = new $modele($document, $detail);
						$cotisations[$key]["details"][] = array("libelle" => $object->getLibelle(), "taux" => $detail->tva, "prix" => $object->getPrix(), "total" => $object->getTotal(), "tva" => $object->getTva(), "quantite" => $object->getQuantite());
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