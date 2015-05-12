<?php

class ExportPDFConfig extends acTCPDFConfig
{
    public function __construct() {
        parent::__construct();
        $this->orientation = self::ORIENTATION_PORTRAIT;
        $this->creator = 'AVA';
        $this->author = 'AVA';
        $this->subject = 'Teledeclaration';
        $this->keywords = 'AVA, Teledeclaration';

        $this->font_name = 'helvetica';
        $this->margin_bottom = $this->margin_footer;

        $this->path_images = sfConfig::get('sf_web_dir').'/images/pdf/';
        $this->header_logo = 'logo.jpg';
        $this->header_logo_width = 40;
    }
}