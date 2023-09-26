<?php
/**
 * Model for Comptabilite
 *
 */

class Comptabilite extends BaseComptabilite {

    const TVA_DEFAUT = 0.2;

    public function getAllIdentifiantsAnalytiquesArrayForCompta() {
        $identifiant_analytique= array();
        $results = array();
        foreach ($this->identifiants_analytiques as $key => $identifiant_analytique) {
            $results[$identifiant_analytique->identifiant_analytique_numero_compte.'_'.$identifiant_analytique->identifiant_analytique] = $identifiant_analytique->identifiant_analytique_libelle_compta;
        }
        return $results;
    }

    public function getTauxTva($identifiant_analytique = null) {
      if (!$this->identifiants_analytiques->exist($identifiant_analytique)) {
        $taux = null;
      } else {
        $taux = $this->identifiants_analytiques->get($identifiant_analytique)->identifiant_analytique_taux_tva;
      }
      return ($taux === null)? self::TVA_DEFAUT : $taux;
    }
}
