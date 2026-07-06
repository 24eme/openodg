<?php

class MouvementFacturesDocument
{
    protected $document;
    protected $hash;

    public function __construct(acCouchdbDocument $document)
    {
        $this->document = $document;
        $this->hash = $document->getMouvementsFactures()->getHash();
    }

    public function getMouvementsFacturesCalculeByIdentifiant($identifiant) {
        $mouvements = $this->document->getMouvementsFacturesCalcule();

        return isset($mouvements[$identifiant]) ? $mouvements[$identifiant] : array();
    }

    public function generateMouvementsFactures() {
        $this->document->clearMouvementsFactures();
        $mouvements = [];

        if (class_exists('RegionConfiguration') && RegionConfiguration::getInstance()->hasOdgProduits()) {
            foreach ($this->document->getRegions() as $r) {
                $mouvements_calcules = $this->document->getMouvementsFacturesCalcule($r);
                if (serialize($mouvements_calcules) == serialize($mouvements)) { // Pour gérer un template de facturation identique pour 2 régions différentes
                    continue;
                }

                $mouvements = array_merge_recursive($mouvements, $mouvements_calcules);
            }
        } else {
            $mouvements = $this->document->getMouvementsFacturesCalcule();
        }

        $this->document->set($this->hash, $mouvements);
    }

    public function facturerMouvements() {
        foreach($this->document->getMouvementsFactures() as $mouvements) {
            foreach($mouvements as $mouvement) {
                $mouvement->facturer();
            }
        }
    }

    public function isFactures() {
      if(!$this->document->exist('mouvements') || !count($this->document->getMouvementsFactures())){
        return false;
      }

      foreach($this->document->getMouvementsFactures() as $mouvements) {
          foreach($mouvements as $mouvement) {
              if($mouvement->isFacture()) {
                  return true;
              }
          }
        }

        return false;
    }

    public function isNonFactures() {
        foreach($this->document->getMouvementsFactures() as $mouvements) {
            foreach($mouvements as $mouvement) {
                if(!$mouvement->isNonFacture()) {
                    return false;
                }
            }
        }

        return true;
    }

    public function findMouvementFactures($cle_mouvement, $part_idetablissement = null){
        foreach($this->document->getMouvementsFactures() as $identifiant => $mouvements) {
	  if ((!$part_idetablissement || preg_match('/^'.$part_idetablissement.'/', $identifiant)) && array_key_exists($cle_mouvement, $mouvements->toArray())) {
                return $mouvements[$cle_mouvement];
            }
        }
        throw new sfException(sprintf('The mouvement %s/%s of the document %s does not exist', $part_idetablissement, $cle_mouvement, $this->document->get('_id')));
    }

    public function getMothersCotisations() {
        $motherDocument = $this->document->getMother();
        $cotisationsArray = [];

        while($motherDocument) {
            $mouvementsFactures = $motherDocument->getMouvementsFactures();

            foreach($mouvementsFactures as $mouvementsFacture) {
                foreach($mouvementsFacture as $mouvement) {
                    $cotisationHash = "/" . "cotisations" . "/". $mouvement->categorie . "/" . "details" . "/" . $mouvement->type_hash;

                    if (array_key_exists($cotisationHash, $cotisationsArray)) {
                        $cotisationsArray[$cotisationHash] += $mouvement->quantite;
                    } else {
                        $cotisationsArray[$cotisationHash] = $mouvement->quantite;
                    }
                }
            }

            $motherDocument = $motherDocument->getMother();
        }

        return $cotisationsArray;
    }
}
