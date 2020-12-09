<?php

class ExportDegustationNonConformitePDF extends ExportPDF {

    protected $degustation = null;
    protected $etablissement = null;
    protected $lot_dossier = null;

    public function __construct($degustation,$etablissement,$lot_dossier, $type = 'pdf', $use_cache = false, $file_dir = null, $filename = null) {
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
        $this->printable_document->addPage($this->getPartial('degustation/degustationNonConformitePDF', array('degustation' => $this->degustation, 'etablissement' => $this->etablissement,'lot_dossier'=>$this->lot_dossier )));
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
      $title = sprintf("Syndicat des Vins IGP des Bouches du Rhône- Antenne d'Aix");
        return $title;
    }

    protected function getFooterText() {
        return "";
    }

    protected function getHeaderSubtitle() {

        return "";
    }


    protected function getConfig() {

        return new ExportDegustationNonConformitePDFConfig();
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
