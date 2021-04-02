<?php

class ExportDegustationNonConformitePDF extends ExportPDF {

    protected $degustation = null;
    protected $etablissement = null;
    protected $lot = null;
    protected $adresse;
    protected $courrierInfos;

    public function __construct($degustation, $lot, $type = 'pdf', $use_cache = false, $file_dir = null, $filename = null) {
        $this->degustation = $degustation;
        $this->lot = $lot;
        $this->etablissement = $lot->getEtablissement();
        $this->adresse = sfConfig::get('app_degustation_courrier_adresse');
        $app = strtoupper(sfConfig::get('sf_app'));
        $courrierInfos = sfConfig::get('app_facture_emetteur');
        $this->courrierInfos = $courrierInfos[$app];
        if (!$filename) {
            $filename = $this->getFileName(true);
        }
        parent::__construct($type, $use_cache, $file_dir, $filename);
        if($this->printable_document->getPdf()){
          $this->printable_document->getPdf()->setPrintHeader(true);
          $this->printable_document->getPdf()->setPrintFooter(true);
        }
    }

    public function create() {
      $this->printable_document->addPage($this->getPartial('degustation/degustationNonConformitePDF_page1', array('degustation' => $this->degustation, 'etablissement' => $this->etablissement, "lot" => $this->lot, 'courrierInfos' => $this->courrierInfos)));
      $this->printable_document->addPage($this->getPartial('degustation/degustationNonConformitePDF_page2', array('degustation' => $this->degustation, 'etablissement' => $this->etablissement, "lot" => $this->lot )));
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
        return '';
    }

    protected function getFooterText() {
        return sprintf("%s     %s - %s  %s    %s\n\n", $this->adresse['raison_sociale'], $this->adresse['adresse'], $this->adresse['cp_ville'], $this->adresse['telephone'], $this->adresse['email']);
    }

    protected function getHeaderSubtitle() {

        return "";
    }


    protected function getConfig() {

        return new ExportLotPDFConfig();
    }

    public function getFileName($with_rev = false) {

        return self::buildFileName($this->degustation, true);
    }

    public static function buildFileName($degustation, $with_rev = false) {
        $filename = sprintf("NON_CONFORMITE_%s", $degustation->_id);
        if ($with_rev) {
            $filename .= '_' . $degustation->_rev;
        }

        return $filename . '.pdf';
    }

}
