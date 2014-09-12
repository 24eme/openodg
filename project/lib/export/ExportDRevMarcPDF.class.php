<?php

class ExportDRevMarcPDF extends ExportPDF {

    protected $drevmarc = null;

    public function __construct($drevmarc, $type = 'pdf', $use_cache = false, $file_dir = null,  $filename = null) {
        $this->drevmarc = $drevmarc;
        if(!$filename) {
            $filename = $this->getFileName(true, true);
        }

        parent::__construct($type, $use_cache, $file_dir, $filename);
    }

    public function create() {
        $this->printable_document->addPage($this->getPartial('drevmarc/pdf', array('drevmarc' => $this->drevmarc)));
    }

    protected function getHeaderTitle() {
        return 'Déclaration de Revendication de Marc 2014';
    }

    protected function getHeaderSubtitle() {

        return "Cave d'Actualys\nCommune de déclaration : Colmar\nDéclaration validée le 30/08/2014";
    }

    protected function getConfig() {

        return new ExportDRevPDFConfig();
    }

    public function getFileName($with_rev = false) {

      return self::buildFileName($this->drevmarc, true, false);
    }

    public static function buildFileName($drevmarc, $with_rev = false) {
        $filename = sprintf("DREVMARC_%s_%s", '7523700100', '2013-2014');

        $declarant_nom = strtoupper(KeyInflector::slugify("DECLARANT"));
        $filename .= '_'.$declarant_nom;

        if($with_rev) {
            $filename .= '_'.$drevmarc->_rev;
        }

        return $filename.'.pdf';
    }
}