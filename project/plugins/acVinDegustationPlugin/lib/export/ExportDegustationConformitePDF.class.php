<?php

class ExportDegustationConformitePDF extends ExportPDF {

    protected $degustation = null;
    protected $etablissement = null;

    public function __construct($degustation,$etablissement, $type = 'pdf', $use_cache = false, $file_dir = null, $filename = null) {
        $this->degustation = $degustation;
        $this->etablissement = $etablissement;

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
        $this->printable_document->addPage($this->getPartial('degustation/degustationConformitePDF', array('degustation' => $this->degustation, 'etablissement' => $this->etablissement )));
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
      $adresse = sfConfig::get('app_degustation_courrier_adresse');
      $title = sprintf($adresse['raison_sociale']);
        return $title;
    }

    protected function getFooterText() {
        return "";
    }

    protected function getHeaderSubtitle() {

        return "";
    }


    protected function getConfig() {

        return new ExportDegustationConformitePDFConfig();
    }

    public function getFileName($with_rev = false) {

        return self::buildFileName($this->degustation, true);
    }

    public static function buildFileName($degustation, $with_rev = false) {
        $filename = sprintf("CONFORMITE_%s", $degustation->_id);
        if ($with_rev) {
            $filename .= '_' . $degustation->_rev;
        }

        return $filename . '.pdf';
    }

}
