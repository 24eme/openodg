<?php

class DRDouaneJsonFile extends DouaneImportCsvFile {

    public function convert() {
    	if (!$this->filePath) {
    		throw new sfException("La cible du fichier n'est pas spécifiée.");
    	}

        $this->etablissement = ($this->doc)? $this->doc->getEtablissementObject() : null;
        if ($this->etablissement && !$this->etablissement->isActif()) {
            return;
        }
        $json = json_decode(file_get_contents($this->filePath));
        $csv = '';
        foreach($json->declarationsRecolteProductionRecoltants as $jsonDr) {
            $this->campagne = $jsonDr->campagne;
            $this->cvi = $jsonDr->numeroCVIRecoltant;
            $doc = $this->getEtablissementRows();
            $drev = $this->getRelatedDrev();
            $has_volume_cave = false;
            $has_volume_coop = false;
            $has_volume_nego = false;
            foreach($jsonDr->declarationProduitsRecoltes->produitsRecoltes as $colonneId => $jsonProduit) {
                $has_volume_familles = $this->getFamilleFromProduit($jsonProduit);
                if($has_volume_familles[0]) {
                    $has_volume_cave = true;
                }
                if($has_volume_familles[1]) {
                    $has_volume_coop = true;
                }
                if($has_volume_familles[2]) {
                    $has_volume_nego = true;
                }
            }
            $famille = $this->getFamilleCalculeeFromLigneDouane($has_volume_cave, $has_volume_coop, $has_volume_nego);
            foreach($jsonDr->declarationProduitsRecoltes->produitsRecoltes as $colonneId => $jsonProduit) {
                $produit = $this->configuration->findProductByCodeDouane($jsonProduit->codeProduit);
                $produitsKey = array(null, null, null, null, null, null, null, $jsonProduit->codeProduit, null);
                if ($produit) {
                    $produitsKey = array($produit->getCertification()->getKey(), $produit->getGenre()->getKey(), $produit->getAppellation()->getKey(), $produit->getMention()->getKey(), $produit->getLieu()->getKey(), $produit->getCouleur()->getKey(), $produit->getCepage()->getKey(), $jsonProduit->codeProduit, $produit->getLibelleComplet());
                }
                $labels = [];
                if(isset($jsonProduit->mentionValorisante) && $jsonProduit->mentionValorisante) {
                    $produitsKey[] = $jsonProduit->mentionValorisante;
                    $labels = DouaneImportCsvFile::extractLabels($jsonProduit->mentionValorisante);
                } else {
                    $produitsKey[] = null;
                }

                $has_volume_familles = $this->getFamilleFromProduit($jsonProduit);

                $startCsvLine = implode(';', $doc).';;;'.implode(';', $produitsKey);
                $endCsvLine = ($colonneId + 1).";".Organisme::getCurrentOrganisme();
                $endCsvLine .= ";".(($produit) ? $produit->getHash() : null);
                $endCsvLine .= ";".(($drev) ? $drev->_id : null);
                $endCsvLine .= ";".(($drev && $drev->hasLotsProduitFilter($produit->getHash())) ? 'FILTERED:'.$drev->_id : null);
                $endCsvLine .= ";".(($this->doc) ? $this->doc->_id : null);
                $endCsvLine .= ';'.$this->getFamilleCalculeeFromLigneDouane($has_volume_familles[0], $has_volume_familles[1], $has_volume_familles[2]);
                $endCsvLine .= ';'.substr($this->campagne, 0, 4);
                $endCsvLine .= ';'.$famille;
                $endCsvLine .= ';'.implode('|', $labels);
                $endCsvLine .= ';'.$this->getHabilitationStatus(HabilitationClient::ACTIVITE_PRODUCTEUR, $produit);

                $correspondanceNumLigneJson = [
                    "04" => "superficieRecolte",
                    "04b" => "superficieRecolte",
                    "05" => "recolteTotale",
                    "09" => "conserveCaveParticuliereExploitant",
                    "10" => "volEnVinification",
                    "13" => "volMcMcrObtenu",
                    "15" => "volVinRevendicableOuCommercialisable",
                    "16" => "volDRAOuLiesSoutirees",
                    "17" => "volEauEliminee",
                    "18" => "vsi",
                    "19" => "vci",
                ];

                foreach($correspondanceNumLigneJson as $code => $jsonKey) {
                    if(isset($jsonProduit->{ $jsonKey }) && $jsonProduit->{ $jsonKey }) {
                        $csv .= $startCsvLine.";".$code.";".DRCsvFile::getCategorieLibelle("DR", $code).";".$jsonProduit->{ $jsonKey }.";;;;;".$endCsvLine."\n";
                    }
                }

                if(isset($jsonProduit->destinationVentesRaisins)) {
                    foreach($jsonProduit->destinationVentesRaisins as $apporteur) {
                        $csv .= $startCsvLine.";06;".DRCsvFile::getCategorieLibelle("DR", "06").";".$apporteur->volObtenuIssuRaisins.";".$apporteur->numeroEvvDestinataire.";;;;".$endCsvLine."\n";
                        if(isset($apporteur->quantiteVenteRaisins) && $apporteur->quantiteVenteRaisins) {
                            $csv .= $startCsvLine.";06kg;".DRCsvFile::getCategorieLibelle("DR", "06kg").";".$apporteur->quantiteVenteRaisins.";".$apporteur->numeroEvvDestinataire.";;;;".$endCsvLine."\n";
                        }
                    }
                }

                if(isset($jsonProduit->destinationVentesMouts)) {
                    foreach($jsonProduit->destinationVentesMouts as $apporteur) {
                        $csv .= $startCsvLine.";07;".DRCsvFile::getCategorieLibelle("DR", "07").";".$apporteur->volObtenuIssuMouts.";".$apporteur->numeroEvvDestinataire.";;;;".$endCsvLine."\n";
                    }
                }

                if(isset($jsonProduit->destinationApportsCaveCoop)) {
                    foreach($jsonProduit->destinationApportsCaveCoop as $coop) {
                        $csv .= $startCsvLine.";08;".DRCsvFile::getCategorieLibelle("DR", "08").";".$coop->volObtenuApportRaisins.";".$coop->numeroEvvCaveCoop.";;;;".$endCsvLine."\n";
                        $csv .= $startCsvLine.";08kg;".DRCsvFile::getCategorieLibelle("DR", "08kg").";".$coop->quantiteApportRaisins.";".$coop->numeroEvvCaveCoop.";;;;".$endCsvLine."\n";
                    }
                }
            }
        }
        return $csv;
    }

    public function getFamilleFromProduit($jsonProduit) {
        $has_volume_cave = isset($jsonProduit->conserveCaveParticuliereExploitant) && $jsonProduit->conserveCaveParticuliereExploitant;
        $has_volume_coop = isset($jsonProduit->destinationApportsCaveCoop) && count($jsonProduit->destinationApportsCaveCoop);
        $has_volume_nego = (isset($jsonProduit->destinationVentesRaisins) && count($jsonProduit->destinationVentesRaisins)) || (isset($jsonProduit->destinationVentesMouts) && count($jsonProduit->destinationVentesMouts));

        return [$has_volume_cave, $has_volume_coop, $has_volume_nego];
    }
}
