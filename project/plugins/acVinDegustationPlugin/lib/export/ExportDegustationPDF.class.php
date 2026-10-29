<?php

abstract class ExportDegustationPDF extends ExportPDF {
    protected $degustation = null;

    public function __construct($degustation, $type = 'pdf', $use_cache = false, $file_dir = null, $filename = null) {
        $this->degustation = $degustation;

        if (!$filename) {
            $filename = $this->getFileName(true);
        }
        parent::__construct($type, $use_cache, $file_dir, $filename);
    }

    protected function getConfig() {
        $configClassName = get_class($this)."Config";
        $config = new $configClassName();
        if($this->degustation->region && file_exists(sfConfig::get('sf_web_dir').'/images/pdf/logo_'.strtolower($this->degustation->region).'.jpg')) {
            $config->header_logo = 'logo_'.strtolower($this->degustation->region).'.jpg';
        }
        $config->header_string = $this->getHeaderSubtitle();
        return $config;
    }

}
