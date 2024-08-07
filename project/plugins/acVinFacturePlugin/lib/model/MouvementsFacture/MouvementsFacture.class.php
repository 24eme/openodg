<?php

/**
 * Model for MouvementsFacture
 *
 */
class MouvementsFacture extends BaseMouvementsFacture implements InterfaceMouvementFacturesDocument {

    protected $mouvement_document = null;

    public function __construct() {
        parent::__construct();
        $this->mouvement_document = new MouvementFacturesDocument($this);
    }

    public function constructIds($date) {
        if (!$date) {
            $date = date('Ymd');
        }

        $this->identifiant = MouvementsFactureClient::getInstance()->getNextNoMouvementsFacture($date);
        $this->periode = substr($date, 0, 6);
        $this->_id = MouvementsFactureClient::getInstance()->getId($this->identifiant);
    }

    public function getMvts() {
        $mvts = [];
        foreach ($this->mouvements as $etbKey => $mvtsEtb) {
            foreach ($mvtsEtb as $mvtKey => $mvt) {
                if (!isset($mvts[$etbKey])) {
                    $mvts[$etbKey] = [];
                }
                $mvts[$etbKey][$mvtKey] = $mvt;
            }
        }
        return $mvts;
    }

    public function getNbMvts() {
        $nb_mvt = 0;
        foreach ($this->getMvts() as $etbKey => $mvtsEtb) {
            $nb_mvt += count($mvtsEtb);
        }
        return $nb_mvt;
    }

    public function getNbMvtsAFacture() {
        $nb_mvt = 0;
        foreach ($this->getMvts() as $etbKey => $mvtsEtb) {
            foreach ($mvtsEtb as $mvtKey => $mvt) {
            if ($mvt->facturable && !$mvt->facture) {
                    $nb_mvt ++;
                }
            }
        }
        return $nb_mvt;
    }

    public function getNbSocietes() {
        return count($this->getMvts());
    }

    public function getTotalHt() {
        $montant = 0;
        foreach ($this->getMvts() as $etbKey => $mvtsEtb) {
            foreach ($mvtsEtb as $mvtKey => $mvt) {
                if ($mvt->facturable) {
                    $montant += $mvt->getPrixHt();
                }
            }
        }
        return $montant;
    }

    public function getTotalHtAFacture() {
        $montant = 0;
        foreach ($this->getMvts() as $etbKey => $mvtsEtb) {
            foreach ($mvtsEtb as $mvtKey => $mvt) {
                if ($mvt->facturable && !$mvt->facture) {
                    $montant += $mvt->getPrixHt();
                }
            }
        }
        return $montant;
    }

    public function getLibelleFromId() {
        return "Facturation libre : " . $this->getLibelle() . " (" . Date::francizeDate($this->getDate()) . ")";
    }

    public function findMouvement($cle_mouvement, $part_id = null){
      $cle_mouvement = rtrim($cle_mouvement);
      foreach($this->document->getMouvements() as $identifiant => $mouvements) {
	       if ((!$part_id || preg_match('/^'.$part_id.'/', $identifiant)) && array_key_exists($cle_mouvement, $mouvements->toArray())) {
            return $mouvements[$cle_mouvement];
          }
        }
        throw new sfException(sprintf('The mouvement %s of the document %s does not exist', $cle_mouvement, $this->document->get('_id')));
    }

    public function getStartIndexForSaisieForm() {
        $index = 0;
        foreach($this->getSortedMvts() as $mvt) {
          if ($mvt->facture) {
            $index++;
          }
        }
        return $index;
    }

    public function getLastMouvement() {
        $mvts = $this->getSortedMvts();
        if (isset($mvts['999_nouveau_nouveau'])) {
          unset($mvts['999_nouveau_nouveau']);
        }
        return end($mvts);
    }

    public function getSortedMvts() {
      $result = array();
      foreach($this->getMvts() as $id => $mvts) {
        foreach($mvts as $key => $mvt) {
          $result[$mvt->getIndexForSaisieForm()] = $mvt;
        }
      }
      ksort($result);
      return $result;
    }


    public function getMouvementsFacturesCalculeByIdentifiant($identifiant) {

        return $this->mouvement_document->getMouvementsFacturesCalculeByIdentifiant($identifiant);
    }

    public function generateMouvementsFactures() {

        return $this->mouvement_document->generateMouvementsFactures();
    }

    public function findMouvementFactures($cle, $id = null){
      return $this->mouvement_document->findMouvementFactures($cle, $id);
    }

    public function facturerMouvements() {

        return $this->mouvement_document->facturerMouvements();
    }

    public function isFactures() {

        return $this->mouvement_document->isFactures();
    }

    public function isNonFactures() {

        return $this->mouvement_document->isNonFactures();
    }

    public function clearMouvementsFactures(){
        $this->remove('mouvements');
        $this->add('mouvements');
    }


    public function getMouvementsFacturesCalcule($region = null) {
      return $this->getMouvementsFactures();
    }

    public function getMouvementsFactures() {
        return $this->_get('mouvements');
    }

}
