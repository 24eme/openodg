<?php

/**
 * Model for ConfigurationCepage
 *
 */
class ConfigurationCepage extends BaseConfigurationCepage {

    const TYPE_NOEUD = 'cepage';

    public function getChildrenNode() {

        return null;
    }

    public function getAppellation() {

        return $this->getCouleur()->getLieu()->getAppellation();
    }

    public function getCertification() {

        return $this->getAppellation()->getCertification();
    }

    public function getGenre() {

        return $this->getAppellation()->getGenre();
    }

    public function getLieu() {

        return $this->getCouleur()->getLieu();
    }

    public function getMention() {

        return $this->getLieu()->getMention();
    }

    public function getCepage() {

        return $this;
    }

    public function getProduitsAll($interpro = null, $departement = null) {

        return array($this->getHash() => $this);
    }

    public function compressDroits() {
        $this->compressDroitsSelf();
    }

    public function getCouleur() {
        return $this->getParentNode();
    }

    public function setDonneesCsv($datas) {
        parent::setDonneesCsv($datas);

        $this->getCouleur()->setDonneesCsv($datas);
        $this->libelle = ($datas[ProduitCsvFile::CSV_PRODUIT_CEPAGE_LIBELLE]) ? $datas[ProduitCsvFile::CSV_PRODUIT_CEPAGE_LIBELLE] : null;
        $this->code = $this->formatCodeFromCsv($datas[ProduitCsvFile::CSV_PRODUIT_CEPAGE_CODE]);

        $this->cepages_autorises = (isset($datas[ProduitCsvFile::CSV_PRODUIT_CEPAGES_AUTORISES]) && $datas[ProduitCsvFile::CSV_PRODUIT_CEPAGES_AUTORISES]) ? explode('|', $datas[ProduitCsvFile::CSV_PRODUIT_CEPAGES_AUTORISES]) : array();

        $this->setDroitDouaneCsv($datas, ProduitCsvFile::CSV_PRODUIT_CEPAGE_CODE_APPLICATIF_DROIT);
        $this->setDroitCvoCsv($datas, ProduitCsvFile::CSV_PRODUIT_CEPAGE_CODE_APPLICATIF_DROIT);
    }

    public function isCepageAutorise($cepage) {
    	return in_array($cepage, $this->cepages_autorises->toArray());
    }

    public function getCorrespondanceHash() {

        return $this->getDocument()->getCorrespondanceHash($this->getHash());
    }

    public function getTypeNoeud() {

        return self::TYPE_NOEUD;
    }

    public function addInterpro($interpro)
    {

        return $this->getParentNode()->addInterpro($interpro);
    }

    public function hasDroits() {
        return true;
    }

    public function hasCodes() {

        return true;
    }

    /* DR */

    public function existRendementByKey($key) {

        return $this->hasRendementByKey($key);
    }

    public function hasLieuEditable() {

        return $this->getParent()->getParent()->getParent()->getParent()->hasLieuEditable();
    }

    public function hasDenomination() {
        if ($this->exist('attributs') && $this->attributs->exist('no_denomination')) {

            return !($this->attributs->no_denomination == 1);
        } elseif ($this->exist('attributs') && $this->attributs->exist('min_quantite') && $this->attributs->get('min_quantite')) {

            return false;
        }

        return true;
    }

    public function hasSuperficie() {
        if ($this->exist('attributs') && $this->attributs->exist('no_superficie')) {

            return !($this->attributs->no_superficie == 1);
        } elseif ($this->exist('attributs') && $this->attributs->exist('min_quantite') && $this->attributs->get('min_quantite')) {

            return false;
        }

        return true;
    }

    public function isSuperficieRequired() {
        if(!$this->hasSuperficie()) {
            return false;
        }

        if ($this->exist('attributs') && $this->attributs->exist('superficie_optionnelle')) {
            return (! $this->attributs->get('superficie_optionnelle'));
        }

        return true;
    }

    public function hasOnlyOneDetail() {
        if ($this->exist('only_one_detail') && $this->get('only_one_detail'))
          return true;
        if ($this->exist('min_quantite') && $this->get('min_quantite'))
          return true;
        return false;
    }
    public function hasMinQuantite()
    {

        return $this->getAttribut('min_quantite');
    }

    public function hasMaxQuantite()
    {

        return $this->getAttribut('max_quantite');
    }

    public function hasNoNegociant()
    {

        return $this->getAttribut('no_negociant');
    }

    public function hasNoCooperative()
    {

        return $this->getAttribut('no_cooperative');
    }

    public function hasNoMout()
    {

        return $this->getAttribut('no_mout');
    }

    public function hasNoMotifNonRecolte()
    {

        return $this->getAttribut('no_motif_non_recolte');
    }

    public function hasTotalCepage() {
        if (!$this->getLieu()->existRendementCepage()) {
            return false;
        }

        return parent::hasTotalCepage();
    }

    public function getRendementNoeud() {

        return $this->getRendementCepage();
    }

    public function existRendement() {
        if($this->getKey() == 'cepage_RB') {

            return false;
        }

        return parent::existRendement();
    }
    /* FIN DR */

}
