<?php

/**
 * Model for Parcellaire
 *
 */
class Parcellaire extends BaseParcellaire {

    protected $declarant_document = null;

    public function __construct() {
        parent::__construct();
        $this->initDocuments();
    }

    public function __clone() {
        parent::__clone();
        $this->initDocuments();
    }

    protected function initDocuments() {
        $this->declarant_document = new DeclarantDocument($this);
    }

    public function storeDeclarant() {
        $this->declarant_document->storeDeclarant();
    }

    public function getEtablissementObject() {

        return EtablissementClient::getInstance()->findByIdentifiant($this->identifiant);
    }

    public function initDoc($identifiant, $campagne) {
        $this->identifiant = $identifiant;
        $this->campagne = $campagne;
        $this->set('_id', ParcellaireClient::getInstance()->buildId($this->identifiant, $this->campagne));
        $this->storeDeclarant();
    }

    public function storeEtape($etape) {
        $this->add('etape', $etape);
    }

    public function isPapier() {
        return $this->exist('papier') && $this->get('papier');
    }
    
   public function hasVendeurs() {
        return count($this->vendeurs);
    }

}
