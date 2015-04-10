<?php

abstract class ExportPDF {

    protected $printable_document;
    protected $document;
    protected $partial_function;
    protected $use_cache;

    public function __construct($type = 'pdf', $use_cache = false, $file_dir = null, $filename = null) {
        $this->setUseCache($use_cache);
        if ($type == 'html') {
            $this->printable_document = new PageableHTML($filename, $file_dir);
        } else {
            $config = $this->getConfig();
            $config->header_title = $this->getHeaderTitle();
            $config->header_string = $this->getHeaderSubtitle();
            $config->title = $this->getTitle();
            $this->printable_document = new PageablePDF($filename, $file_dir, $config);
        }
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

    public function getFile() {

        return $this->printable_document->getFile();
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

    protected function setUseCache($use) {
        $this->use_cache = $use;
    }

    protected function getConfig() {

        return new ExportDRevPDFConfig();
    }

    abstract protected function create();

    protected function getTitle() {

        return sprintf('%s de %s', $this->getHeaderTitle(), preg_replace('/\n/', ', ', $this->getHeaderSubtitle()));
    }

    abstract protected function getHeaderTitle();

    abstract protected function getHeaderSubtitle();
}