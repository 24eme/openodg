<?php

class ExportDegustationEtiquettesPrlvmtPDF extends ExportPDF {

    protected $degustation = null;
    protected $identifiant = null;

    public function __construct($degustation, $identifiant = null, $anonymat4labo = false, $type = 'pdf', $use_cache = false, $file_dir = null, $filename = null) {
        $this->degustation = $degustation;
        $this->identifiant = $identifiant;
        $this->anonymat4labo = $anonymat4labo;
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

    public function create() {
      foreach ($this->degustation->getEtiquettesFromLots(7, $this->identifiant) as $plancheLots) {
        $this->printable_document->addPage($this->getPartial('degustation/etiquettesPrlvmtPdf', array('degustation' => $this->degustation, 'plancheLots' => $plancheLots, 'anonymat4labo' => $this->anonymat4labo)));
      }
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

        return new ExportDegustationEtiquettesPDFConfig();
    }

    public function getFileName($with_rev = false) {

        return self::buildFileName($this->degustation, true);
    }

    public static function buildFileName($degustation, $with_rev = false) {
        $filename = sprintf("table_des_etiquettes_des_lots_preleves_%s", $degustation->_id);


        if ($with_rev) {
            $filename .= '_' . $degustation->_rev;
        }


        return $filename . '.pdf';
    }

}
