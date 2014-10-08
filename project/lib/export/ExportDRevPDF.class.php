<?php

class ExportDRevPDF extends ExportPDF {

    protected $drev = null;

    public function __construct($drev, $type = 'pdf', $use_cache = false, $file_dir = null,  $filename = null) {
        $this->drev = $drev;
        if(!$filename) {
            $filename = $this->getFileName(true, true);
        }

        parent::__construct($type, $use_cache, $file_dir, $filename);
    }

    public function create() {
        $this->printable_document->addPage($this->getPartial('drev/pdf', array('drev' => $this->drev)));
        $this->printable_document->addPage($this->getPartial('drev/pdfCepages', array('drev' => $this->drev)));
        $this->printable_document->addPage($this->getPartial('drev/pdfLots', array('drev' => $this->drev)));
    }

    protected function getHeaderTitle() {
        return 'Déclaration de Revendicaton 2014';
    }

    protected function getHeaderSubtitle() {

        return "Cave d'Actualys\nCommune de déclaration : Colmar\nDéclaration validée le 30/08/2014";
    }

    protected function getConfig() {

        return new ExportDRevPDFConfig();
    }

    public function getFileName($with_rev = false) {

      return self::buildFileName($this->drev, true, false);
    }

    public static function buildFileName($drev, $with_rev = false) {
        $filename = sprintf("DREV_%s_%s", '7523700100', '2013-2014');

        $declarant_nom = strtoupper(KeyInflector::slugify("DECLARANT"));
        $filename .= '_'.$declarant_nom;

        if($with_rev) {
            $filename .= '_'.$drev->_rev;
        }

        return $filename.'.pdf';
    }
}