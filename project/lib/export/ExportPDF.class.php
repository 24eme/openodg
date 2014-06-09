<?php

abstract class ExportPDF {

    protected $printable_document;
    protected $document;
    protected $partial_function;
    protected $use_cache;
    protected $type = 'type';

    public function __construct($type = 'pdf', $use_cache = false, $file_dir = null, $filename = null) {
        $this->setType($type);
        $this->setUseCache($use_cache);
        $this->printable_document = $this->getInstancePrintableDocument($file_dir, $filename);
        $this->configure();
    }

    public function isUseCache() {
        
        return $this->use_cache;
    }

    public function setPartialFunction($function) {
        
        $this->partial_function = $function;
    }

    public function isCached() {

        return ($this->printable_document->isCached());
    }

    public function removeCache() {
        
        return $this->printable_document->removeCache();
    }

    public function generate() {
        if(!$this->isUseCache() || !$this->isCached()) {
          $this->create();
        }

        return $this->printable_document->generate(true);
    }

    public function addHeaders($response) {
        $this->printable_document->addHeaders($response);
    }

    public function output() {
        
        return $this->printable_document->output();
    }

    protected function getPartial($templateName, $vars = null) {
        if(!$this->partial_function) {
            throw new sfException('Partial function is not defined use ->setPartialFunction');
        }

        return call_user_func_array($this->partial_function, array($templateName, $vars));
    }

    protected function getInstancePrintableDocument($file_dir, $filename) {
        $class = 'PageablePDF';
        
        if ($this->type == 'html') {
          $class = 'PageableHTML';
        }

        return new $class($filename, $file_dir);
    }

    protected function configure() {
        
        $pdf = $this->printable_document->getPdf();
        $pdf->SetCreator('AVA');
        $pdf->SetAuthor('AVA');
        $pdf->SetTitle($this->getTitle().' de '.preg_replace('/\n/', ', ', $this->getSubtitle()));
        $pdf->SetSubject('PDF AVA');
        $pdf->SetKeywords('Declaration, Revendication, AVA');

        $pdf->SetHeaderData('logo.jpg', 40, $this->getTitle(), $this->getSubtitle());
    }

    protected function setType($type) {
        $this->type = $type;
    }

    protected function setUseCache($use) {
        $this->use_cache = $use;
    }

    abstract protected function create();

    abstract protected function getTitle();

    abstract protected function getSubtitle();
}