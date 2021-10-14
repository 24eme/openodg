<?php
/**
 * Model for DegustationPrelevement
 *
 */

class DegustationPrelevement extends BaseDegustationPrelevement {

    public function getLibelleComplet() {

        return trim($this->libelle_produit . " " . $this->libelle . " " . $this->getDocument()->getMillesime());
    }

    public function getConfigProduit() {
        if(!$this->hash_produit || !$this->getDocument()->getConfiguration()->exist($this->hash_produit)) {

            return null;
        }

        return $this->getDocument()->getConfiguration()->get($this->hash_produit);
    }

    public function updateLibelleProduit() {
        if(!$this->getConfigProduit()) {
            $this->libelle_produit = null;

            return;
        }

        $this->libelle_produit = $this->getConfigProduit()->getCouleur()->getLibelleComplet();
    }

    public function getLibelleProduit() {
        if(!$this->_get('libelle_produit')) {
            $this->updateLibelleProduit();
        }

        return $this->_get('libelle_produit');
    }

    public function generateAnonymatPrelevementComplet() {
        if(!$this->anonymat_prelevement) {
            $this->anonymat_prelevement_complet = null;
        }

        $code_cepage = $this->getCodeCepage();

        if(!$code_cepage) {
            $code_cepage = $this->getCodeCepageEmpty();
        }

        $this->anonymat_prelevement_complet = sprintf("%s %03d %03X", $code_cepage, $this->anonymat_prelevement, $this->anonymat_prelevement + 2560);
    }

    public function setAnonymatPrelevement($value) {
        $return = $this->_set('anonymat_prelevement', $value);

        $this->generateAnonymatPrelevementComplet();

        return $return;
    }

    public function getCodeCepageEmpty() {
        $code_cepage = '__';

        if($this->vtsgn) {
            $code_cepage .= '__';
        }

        return $code_cepage;
    }

    public function getCodeCepage() {
        $code_cepage = substr($this->hash_produit, -2);
        if(!$code_cepage) {

            return;
        }
        if($this->vtsgn) {
            $code_cepage .= $this->vtsgn;
        }

        return $code_cepage;
    }

    public function isPreleve() {
        if($this->preleve && $this->hash_produit && $this->isDeguste() && $this->commission) {

            return true;
        }

        return $this->preleve && $this->hash_produit && !is_null($this->cuve) && $this->cuve != "";
    }

    public function isAffectationTerminee() {

        return $this->commission && $this->isDeguste();
    }

    public function isDeguste() {

        return !is_null($this->anonymat_degustation);
    }

    public function getCuveNettoye() {

        return trim(str_replace('/', '', $this->cuve));
    }

    public function hasMauvaiseNote() {
        foreach($this->notes as $note) {
            if($note->isMauvaiseNote()) {
                return true;
            }
        }

        return false;
    }

    public function isDegustationTerminee() {
        foreach($this->notes as $note) {
            if($note->note === null) {
                return false;
            }
        }

        return true;
    }
}
