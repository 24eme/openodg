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
        $this->document->set($this->hash, $this->document->getMouvementsFacturesCalcule());
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
}
