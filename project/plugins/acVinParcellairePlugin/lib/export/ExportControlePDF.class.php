<?php

class ExportControlePDF extends ExportPDF {

    protected $controle = null;
    protected $identifiant = null;
    protected $parcellaire = null;
    protected $potentiel = null;
    protected $etablissement = null;
    protected $compte = null;
    protected $intentionParcellaire = null;
    protected $parcellaireManquant = null;

    public function __construct($controle, $identifiant = null, $type = 'pdf', $use_cache = false, $file_dir = null, $filename = null) {
        $this->controle = $controle;
        $this->identifiant = $identifiant;

        $this->parcellaire = ParcellaireClient::getInstance()->getLast($this->identifiant);
        $this->potentiel = PotentielProduction::retrievePotentielProductionFromParcellaire($this->parcellaire);

        $this->etablissement = $this->controle->getEtablissementObject();
        $this->compte = $this->etablissement->getMasterCompte();

        $this->intentionParcellaire = ParcellaireIntentionClient::getInstance()->getLast($this->etablissement->identifiant);

        $this->parcellaireManquant = ParcellaireManquantClient::getInstance()->getLast($this->identifiant);

        if (!$filename) {
            $filename = $this->getFileName(true);
        }
        parent::__construct($type, $use_cache, $file_dir, $filename);
        if($this->printable_document->getPdf()){
          $this->printable_document->getPdf()->setViewerPreferences(array("PrintScaling" => "None"));
          $this->printable_document->getPdf()->setPrintHeader(false);
          $this->printable_document->getPdf()->setPrintFooter(false);
        }
    }

    public function create()
    {
        $ppproduits = array();
        $controleHash = $this->controle->getProduitsHash();
        foreach ($this->potentiel->getProduits() as $ppproduit) {
            if (! in_array($ppproduit->getProduitHash(), $controleHash)) {
                continue;
            }
            $ppproduits[$ppproduit->getLibelle()] = $ppproduit->getSuperficieMax();
        }

        $hasVIFA = 'N';
        if ($this->compte->tags->exist('manuel')) {
            if (in_array('convention_vifa', $this->compte->tags->manuel->toArray())) {
                $hasVIFA = 'O';
            }
        }

        if ($this->intentionParcellaire) {
            $dgc = str_replace(' ', '&nbsp;', implode(', ', $this->intentionParcellaire->getDgc()));
        } else {
            $dgc = 'N';
        }

        $manquant = 'N';
        if ($this->parcellaireManquant) {
            $manquant = $this->getManquants() == 0 ? 'N' : 'O';
        }

        $this->printable_document->addPage($this->getPartial('controle/controlePdf', array('controle' => $this->controle, 'parcellaire' => $this->parcellaire, 'ppproduits' => $ppproduits, 'hasVIFA' => $hasVIFA, 'dgc' => $dgc, 'manquant' => $manquant)));
    }


    public function output() {
        if($this->printable_document instanceof PageableHTML) {

            return parent::output();
        }

        return file_get_contents($this->getFile());
    }

    public function getFile() {

        if($this->printable_document instanceof PageableHTML) {
            return parent::getFile();
        }

        return sfConfig::get('sf_cache_dir').'/pdf/'.$this->getFileName(true);
    }

    protected function getHeaderTitle() {
        return "";
    }

    protected function getFooterText() {
        return "";
    }

    protected function getHeaderSubtitle() {

        return "";
    }


    protected function getConfig() {

        return new ExportControlePDFConfig();
    }

    public function getFileName($with_rev = false) {

        return self::buildFileName($this->controle, true);
    }

    public static function buildFileName($controle, $with_rev = false) {
        $filename = sprintf("controle_%s", $controle->_id);

        if ($with_rev) {
            $filename .= '_' . $controle->_rev;
        }


        return $filename . '.pdf';
    }

    public function getManquants()
    {
        $ret = 0;
        foreach ($this->controle->parcelles as $id_parcelle => $info_parcelle) {
            $ret += $this->parcellaireManquant->getPourcentageFromIdParcelle($id_parcelle);
        }
        return $ret;
    }

}
