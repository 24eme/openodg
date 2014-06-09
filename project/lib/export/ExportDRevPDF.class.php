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
        $this->printable_document->addPage("");
    }

    protected function getTitle() {
        return 'Déclaration de Revendicaton';
    }

    protected function getSubtitle() {

        return '';
    }

    public function getFileName($with_rev = false) {

      return self::buildFileName($this->drev, true, false);
    }

    public static function buildFileName($drev, $with_rev = false) {
        $filename = sprintf("DR_%s_%s", '7523700100', '2013-2014');

        $declarant_nom = strtoupper(KeyInflector::slugify("DECLARANT"));
        $filename .= '_'.$declarant_nom;

        if($with_rev) {
            $filename .= '_'.$drev->_rev;
        }

        return $filename.'.pdf';
    }
}