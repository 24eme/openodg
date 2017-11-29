<?php

/**
 * Model for ConfigurationDeclaration
 *
 */
class ConfigurationDeclaration extends BaseConfigurationDeclaration {

    const TYPE_NOEUD = 'declaration';

    public function getChildrenNode() {

        return $this->certifications;
    }

    public function setDonneesCsv($datas) {

    }

    public function getDatesDroits($interpro = "INTERPRO-declaration") {
        if(is_null($this->dates_droits)) {
            $this->dates_droits = $this->loadDatesDroits($interpro);
        }

        return $this->dates_droits;
    }

    public function getDroits($interpro) {

        return null;
    }

    public function hasDroits() {

        return false;
    }

    public function getCodeProduit() {
        if(!$this->exist('code_produit')) {

            return null;
        }

        return $this->_get('code_produit');
    }

    public function getCodeComptable() {
        if(!$this->exist('code_comptable')) {

            return null;
        }

        return $this->_get('code_comptable');
    }

    protected function compressDroitsSelf() {

        return null;
    }

    public function getTypeNoeud() {

        return self::TYPE_NOEUD;
    }

    public function getDensite() {
        if (!$this->exist('densite') || !$this->_get('densite')) {

            return $this->getParentNode()->getDensite();
        }

        return $this->_get('densite');
    }

    public function getFormatLibelle() {

       return "%g% %a% %m% %l% %co% %ce%";
    }

    public function getLibelles() {

        return null;
    }

    public function getCodes() {

        return null;
    }

    /* DR */
    public function hasNoUsagesIndustriels() {

        return ($this->exist('no_usages_industriels') && $this->get('no_usages_industriels'));
    }

    public function hasNoRecapitulatifCouleur() {

        return ($this->exist('no_recapitulatif_couleur') && $this->get('no_recapitulatif_couleur'));
    }

    public function getRendementAppellation() {

        return 0;
    }

    public function getRendementCouleur() {

        return 0;
    }

    public function getRendement() {

        return 0;
    }

    public function getRendementVci()  {

        return 0;
    }

    public function hasMout() {

        return false;
    }

    public function hasTotalCepage() {

        return true;
    }

    public function hasVtsgn() {

        return true;
    }

    public function isAutoDs() {

        return false;
    }

    /* FIN DR */
}
