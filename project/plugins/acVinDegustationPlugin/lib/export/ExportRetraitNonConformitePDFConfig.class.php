<?php

class ExportRetraitNonConformitePDFConfig extends ExportPDFConfig
{
    public function __construct() {
        parent::__construct();
        $this->subject = 'Levée de Non-conformité';
        $this->orientation = self::ORIENTATION_PORTRAIT;
        $this->keywords = 'Degustation';
        $this->creator = 'IGP';
        $this->author = 'IGP';

        $this->font_name = 'helvetica';
        $this->margin_left = 15;
        $this->margin_top = 30;
        $this->margin_right = 15;
        $this->margin_bottom = 20;
        $this->margin_header = 10;
        $this->margin_footer = 0;
        $this->font_size = 8;
        $this->font_size_main = 8;

        $this->path_images = sfConfig::get('sf_web_dir').'/images/pdf/';
        $this->header_logo = '';
        $this->header_logo_width = 0;

    }
}
