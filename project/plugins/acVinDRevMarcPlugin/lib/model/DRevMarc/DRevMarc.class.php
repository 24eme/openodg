<?php

/**
 * Model for DRev
 *
 */
class DRevMarc extends BaseDRevMarc {

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

    public function constructId() {
        $this->set('_id', 'DREVMARC-' . $this->identifiant . '-' . $this->campagne);
    }

    public function initDrevMarc($identifiant, $campagne) {
        $this->identifiant = $identifiant;
        $this->campagne = $campagne;
    }

    public function storeDeclarant() {
        $this->declarant_document->storeDeclarant();
    }
    
    public function storeEtape($etape) {
    	$this->add('etape', $etape);
    }

    public function getEtablissementObject() {

        return EtablissementClient::getInstance()->findByIdentifiant($this->identifiant);
    }
    
    public function validate() {
        $this->validation = date('Y-m-d');
    }

}