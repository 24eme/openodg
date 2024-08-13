<?php

/**
 * Model for FactureMouvement
 *
 */
class FactureMouvement extends BaseFactureMouvement {

    public function updateIdentifiantAnalytique($identifiant_analytique) {
        $comptabiliteDoc = ComptabiliteClient::getInstance()->findCompta();
        $node_analytique = null;
        $identifiant_analytique_Keys = explode('_',$identifiant_analytique);
        foreach ($comptabiliteDoc->get('identifiants_analytiques') as $analytiquesCompta) {
          if(($analytiquesCompta->identifiant_analytique == $identifiant_analytique_Keys[1])
           && ($analytiquesCompta->identifiant_analytique_numero_compte == $identifiant_analytique_Keys[0])){
             $node_analytique = $analytiquesCompta;
             break;
           }
        }
        $comptabiliteDoc->get('identifiants_analytiques')->get($node_analytique->getKey());
        if (!$node_analytique) {
            throw new sfException("L'identifiant analytique $identifiant_analytique n'existe pas dans le document de COMPTABILITE");
        }
        $this->setIdentifiantAnalytique($identifiant_analytique);
        $this->setIdentifiantAnalytiqueLibelleCompta($node_analytique->identifiant_analytique_libelle_compta);
        $this->tva = $node_analytique->identifiant_analytique_taux_tva;
    }

    public function getIndexForSaisieForm() {
      $numOrdre = 0;
      if ($this->getKey() == 'nouveau') {
        $numOrdre = '999';
      }
      return sprintf('%03d', $numOrdre).'_'.$this->getParent()->getKey().'_'.$this->getKey();
    }

    public function getPrixHt() {
        return $this->getQuantite() * $this->getPrixUnitaire();
    }

    public function setTypeLibelle($s) {
        $this->categorie = substr(sha1($s), 0, 8);
        return $this->_set('type_libelle', $s);
    }

}
