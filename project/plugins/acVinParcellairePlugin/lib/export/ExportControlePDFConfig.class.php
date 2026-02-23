<?php

class ExportControlePDFConfig extends ExportPDFConfig
{
    public function __construct() {
        parent::__construct();
        $this->subject = 'controle';
        $this->orientation = self::ORIENTATION_PORTRAIT;
        $this->keywords = 'Controle';
        $this->creator = 'ODG';
        $this->author = 'ODG';

        $this->font_name = 'helvetica';
        $this->margin_left = 0;
        $this->margin_top = 4.7;
        $this->margin_right = 0;
        $this->margin_bottom = 0;
        $this->margin_header = 0;
        $this->margin_footer = 0;
        $this->font_size = 10;
        $this->font_size_main = 10;

        $this->path_images = sfConfig::get('sf_web_dir').'/images/pdf/';
        $this->header_logo = 'logo_'.sfConfig::get('sf_app').'.jpg';
        $this->header_logo_width = 20;
    }
}
