<?php
/**
 * Model for ParcellaireIntentionAffectation
 *
 */

class ParcellaireIntentionAffectation extends ParcellaireAffectation {

  protected function getTheoriticalId() {
    $date = str_ireplace("-","",$this->date);
    return ParcellaireIntentionAffectationClient::TYPE_COUCHDB.'-'.$this->identifiant.'-'.$date;
  }

  public function constructId() {
      $id = $this->getTheoriticalId();
      $this->set('_id', $id);
  }
  public function initDoc($identifiant, $periode, $date) {
      $this->identifiant = $identifiant;
      if ($this->exist('date')) {
        $this->date = $date;
        $this->updateValidationDoc();
      }
      $this->campagne = $periode.'-'.($periode + 1);
      $this->constructId();
  }

  public function getPeriode() {
      return preg_replace('/-.*/', '', $this->campagne);
  }

  public function updateValidationDoc() {
      $this->validation = $this->date;
      $this->validation_odg = $this->date;
  }

  public function updateParcelles() {
      foreach($this->declaration->getParcelles() as $p) {
          $p->updateFromParcellaire();
      }
  }

  public function getDenominationAire() {
      return "AOC Sainte-Victoire";
  }

  public function updateIntentionFromParcellaireAndLieux() {
      $parcellaire = $this->getParcellaire();
      if ($parcellaire) {
          $parcellesFromParcellaire = $parcellaire->getParcelles();
      }
      if (!$parcellesFromParcellaire || !count($parcellesFromParcellaire)) {
          return;
      }
      $denominationscommunes = sfConfig::get('app_communes_denominations');
      $denominations = [];
      foreach($denominationscommunes as $hash => $code_communes) {
          $hash = str_replace('/declaration/', '', $hash);
          foreach($code_communes as $code) {
              if (!isset($denominations[$code])) {
                  $denominations[$code] = [];
              }
              $denominations[$code][] = $hash;
          }
      }
      $libelleProduits = array();
      $affectees = array();
      $already_seen = array();
      foreach ($this->declaration->getParcelles() as $parcelle) {
          if (!$parcelle->affectation) {
              $parcelle->remove('superficie_affectation');
              $parcelle->superficie = null;
              continue;
          }

          $pMatch = $parcellaire->findParcelle($parcelle, 1, true, $already_seen);

          if(!$pMatch) {
              continue;
          }
          $affectees[$parcelle->getProduit()->getHash()][$pMatch->getHash()] = array('date' => $parcelle->date_affectation, 'superficie' => $parcelle->superficie);
      }
      $this->remove('declaration');
      $this->add('declaration');

      foreach ($parcellesFromParcellaire as $parcelle) {
          if (isset($denominations[$parcelle->code_commune])) {
            foreach ($denominations[$parcelle->code_commune] as $lieu_hash) {
              if (!$this->getConfiguration()->declaration->exist($lieu_hash)) {
                  continue;
              }
              if (!isset($libelleProduits[$lieu_hash])) {
                  $libelleProduits[$lieu_hash] = $this->getConfiguration()->declaration->get($lieu_hash)->getLibelleFormat();
              }
              if ($this->declaration->exist($lieu_hash)) {
                  $item = $this->declaration->get($lieu_hash);
              } else {
                  $item = $this->declaration->add($lieu_hash);
                  $item->libelle = $libelleProduits[$lieu_hash];
              }
              if ($item->detail->exist($parcelle->getParcelleId())) {
                  continue;
              }
              $subitem = $item->detail->add($parcelle->getParcelleId());
              ParcellaireClient::CopyParcelle($subitem, $parcelle, true);
              $subitem->active = 1;
              $subitem->remove('vtsgn');
              if($parcelle->exist('vtsgn')) {
                  $subitem->add('vtsgn', (int)$parcelle->vtsgn);
              }
              $subitem->campagne_plantation = ($parcelle->exist('campagne_plantation'))? $parcelle->campagne_plantation : null;
              if (isset($affectees[$item->getHash()][$parcelle->getHash()]) && $affectees[$item->getHash()][$parcelle->getHash()]) {
                  $subitem->affectation = 1;
                  $subitem->superficie  = $affectees[$item->getHash()][$parcelle->getHash()]['superficie'];
                  $subitem->date_affectation = $affectees[$item->getHash()][$parcelle->getHash()]['date'];
                  if ($subitem->date_affectation == "2004-05-29") {
                      $subitem->date_affectation = "2005-05-29";
                  }
              } else if (in_array($parcelle->isInDenominationLibelle($this->getDenominationAire()), [AireClient::PARCELLAIRE_AIRE_TOTALEMENT, AireClient::PARCELLAIRE_AIRE_PARTIELLEMENT])) {
                  $subitem->affectation = 1;
                  $subitem->date_affectation = "2005-05-29";
                  if ($subitem->campagne_plantation > "2004-2005") {
                      $subitem->date_affectation = substr($subitem->campagne_plantation, 6, 4). "-08-01";
                  }
                  if (isset($affectees[$item->getHash()][$parcelle->getHash()]) && $affectees[$item->getHash()][$parcelle->getHash()] && $parcelle->getSuperficieParcellaire() != $affectees[$item->getHash()][$parcelle->getHash()]['superficie']) {
                      $subitem->superficie = $affectees[$item->getHash()][$parcelle->getHash()]['superficie'];
                  }
              } else {
                $subitem->affectation = 0;
                $subitem->superficie = $parcelle->superficie;
              }
              $subitem->origine_doc = $parcelle->getDocument()->_id;
              $subitem->origine_hash = $parcelle->getHash();
            }
         }
      }
  }

  public function getDocumentDefinitionModel() {
      return 'ParcellaireIntentionAffectation';
  }

    public function getDateFr() {
        $date = new DateTime($this->date);

        return $date->format('d/m/Y');
    }

    protected function initDocuments() {
        $this->declarant_document = new DeclarantDocument($this);
    }

    public function save() {
        $this->cleanNonAffectee();
        return parent::save();
    }

    public function cleanNonAffectee() {
        $todelete = [];
        foreach($this->declaration->getParcelles() as $id => $p) {
            if ($p->affectation) {
                continue;
            }
            $todelete[] = $p;
        }
        foreach($todelete as $p) {
            $this->remove($p->getHash());
        }
    }

    protected function doSave() {
    	if ($this->isNew()) {
    		if ($last = ParcellaireIntentionClient::getInstance()->getLast($this->identifiant)) {
    			$last->add('lecture_seule', true);
    			$last->save();
    		}
    	}
    }

}
