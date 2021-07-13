<?php
class ExportDeclarationLotsPDF extends ExportPDF {

    protected $declaration = null;

    public function __construct($declaration, $type = 'pdf', $use_cache = false, $file_dir = null, $filename = null) {

        $this->declaration = $declaration;
        if (!$filename) {
            $filename = $this->getFileName(true);
        }

        parent::__construct($type, $use_cache, $file_dir, $filename);
        if($this->printable_document->getPdf()){
          $this->printable_document->getPdf()->setPrintHeader(true);
          $this->printable_document->getPdf()->setPrintFooter(true);
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
        return '';
    }


    protected function getConfig() {

        return new ExportDeclarationLotsPDFConfig();
    }

    protected function getFooterText() {
        return sprintf("<br/>%s     %s - %s - %s<br/>%s    %s", Organisme::getInstance(null, 'degustation')->getNom(), Organisme::getInstance(null, 'degustation')->getAdresse(), Organisme::getInstance(null, 'degustation')->getCodePostal(), Organisme::getInstance(null, 'degustation')->getCommune(), Organisme::getInstance(null, 'degustation')->getTelephone(), Organisme::getInstance(null, 'degustation')->getEmail());
    }

    protected function getHeaderSubtitle() {
        return '';
    }

    public function getFileName($with_rev = false) {
        return self::buildFileName($this->declaration, true);
    }

    public static function buildFileName($declaration, $with_rev = false) {
        $filename = $declaration->_id;
        if ($with_rev) {
            $filename .= '_' . $declaration->_rev;
        }
        return $filename . '.pdf';
    }

    protected function create() { throw new Exception('create method not implemented'); }
}
