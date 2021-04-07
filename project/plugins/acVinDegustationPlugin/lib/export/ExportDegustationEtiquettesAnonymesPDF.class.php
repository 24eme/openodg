<?php

class ExportDegustationEtiquettesAnonymesPDF extends ExportPDF {

    protected $degustation = null;
    const MAX_PLANCHE = 24;

    public function __construct($degustation, $type = 'pdf', $use_cache = false, $file_dir = null, $filename = null) {
        $this->degustation = $degustation;

        if (!$filename) {
            $filename = $this->getFileName(true);
        }
        parent::__construct($type, $use_cache, $file_dir, $filename);
        if($this->printable_document->getPdf()){
          $this->printable_document->getPdf()->setPrintHeader(false);
          $this->printable_document->getPdf()->setPrintFooter(false);
        }
    }

    public function create() {
        $i = 0;
        $plancheLots = [];
        $lots = $this->degustation->getLots()->toArray();

        usort($lots, function ($a, $b) {
            return strcmp($a->numero_anonymat, $b->numero_anonymat);
        });

        foreach ($lots as $lot) {
            $plancheLots[] = $lot;
            $i++;

            if ($i == self::MAX_PLANCHE) {
                $this->printable_document->addPage($this->getPartial('degustation/etiquettesAnonymesPDF', array('degustation' => $this->degustation, 'plancheLots' => $plancheLots)));
                $i = 0;
                $plancheLots = [];
            }
        }
        $this->printable_document->addPage($this->getPartial('degustation/etiquettesAnonymesPDF', array('degustation' => $this->degustation, 'plancheLots' => $plancheLots)));
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
        $filename = sprintf("table_des_etiquettes__des_lots_anonymises_%s", $degustation->_id);


        if ($with_rev) {
            $filename .= '_' . $degustation->_rev;
        }


        return $filename . '.pdf';
    }

}
