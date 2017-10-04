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

	// public function generateCotisations($identifiant_or_compte, $campagne, $force = false)
	// {
	// 	$template = $this;
	// 	$compte = $identifiant_or_compte;
	//
	// 	if(is_string($compte)) {
	// 		$compte = CompteClient::getInstance()->findByIdentifiant($identifiant_or_compte);
	// 	}
	//
	// 	$cotisations = array();
	// 	foreach ($this->docs as $doc) {
	// 		$documents = $this->getDocumentFacturable($doc, $compte->identifiant, $campagne);
	// 		if(!count($documents)) {
	//
	// 			throw new sfException(sprintf("Le document %s n'a pas été trouvé (%s-%s-%s)", strtoupper($doc), strtoupper($doc), str_replace("E", "", $compte->identifiant), $campagne));
	// 		}
	//
	// 		$mouvements = array();
	// 		foreach($documents as $doc) {
	// 			if(!count($doc->mouvements)) {
	// 				$document->generateMouvements();
	// 				$document->save();
	// 			}
	//
	// 			$mouvement = $doc->findMouvement($this->_id, $compte->identifiant);
	//
	// 			if(!$mouvement) {
	//
	// 				continue;
	// 			}
	//
	// 			if((!$mouvement->isFacturable() || $mouvement->facture) && !$force) {
	// 				continue;
	// 			}
	//
	// 			$mouvements[] = $mouvement;
	// 		}
	//
	// 		foreach ($this->cotisations as $key => $cotisation) {
	//
	// 			$modele = $cotisation->modele;
	//
	// 			$object = new $modele($compte, $cotisation->callback);
	// 			$details = $object->getDetails($cotisation->details);
	//
	// 			if (!in_array($cotisation->libelle, array_keys($cotisations))) {
	// 				$cotisations[$key] = array();
	// 				$cotisations[$key]["libelle"] = $cotisation->libelle;
	// 				$cotisations[$key]["code_comptable"] = $cotisation->code_comptable;
	// 				$cotisations[$key]["details"] = array();
	// 				$cotisations[$key]["origines"] = array();
	// 			}
	// 			foreach ($details as $type => $detail) {
	// 				$modele = $detail->modele;
	// 				$object = new $modele($template, $mouvements, $detail);
	//
	// 				if ($key == 'syndicat_viticole') {
	// 					$cotisations[$key]["details"][] = array("libelle" => $object->getLibelle(), "taux" => $detail->tva, "prix" => $object->getTotal(), "total" => $object->getTotal(), "tva" => $object->getTva(), "quantite" => 1);
	// 				} else {
	// 					$cotisations[$key]["details"][] = array("libelle" => $object->getLibelle(), "taux" => $detail->tva, "prix" => $object->getPrix(), "total" => $object->getTotal(), "tva" => $object->getTva(), "quantite" => $object->getQuantite());
	// 				}
	//
	// 				foreach($mouvements as $mouvement) {
	// 					$cotisations[$key]["origines"][$mouvement->getDocument()->_id] = array($this->_id);
	// 				}
	// 			}
	// 		}
	// 	}
	// 	return $cotisations;
	// }

	public function getMouvements($compteIdentifiant, $force = false) {
		$mouvements = array();
		foreach ($this->docs as $docModele) {
			$documents = $this->getDocumentFacturable($docModele, $compteIdentifiant, $this->getCampagne());

			foreach($documents as $doc) {
				if(!count($doc->mouvements)) {
					$document->generateMouvements();
					$document->save();
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
