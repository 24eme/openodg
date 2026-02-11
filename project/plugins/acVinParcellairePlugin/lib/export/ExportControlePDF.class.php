<?php

class ExportControlePDF extends ExportPDF {

    protected $controle = null;
    protected $identifiant = null;
    protected $parcellaire = null;
    protected $potentiel = null;

    public function __construct($controle, $identifiant = null, $type = 'pdf', $use_cache = false, $file_dir = null, $filename = null) {
        $this->controle = $controle;
        $this->identifiant = $identifiant;

        $this->parcellaire = ParcellaireClient::getInstance()->getLast($this->identifiant);
        $this->potentiel = PotentielProduction::retrievePotentielProductionFromParcellaire($this->parcellaire);

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
        $this->printable_document->addPage($this->getPartial('controle/controlePdf', array('controle' => $this->controle)));
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

}
