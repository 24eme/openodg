<?php

class ExportDeclarationLotsPDFConfig extends acTCPDFConfig
{
    public function __construct() {
        parent::__construct();
        $this->subject = '';
        $this->orientation = self::ORIENTATION_PORTRAIT;
        $this->keywords = 'Degustation';
        $this->creator = 'IGP';
        $this->author = 'IGP';

        $this->font_name = 'helvetica';
        $this->margin_left = 15;
        $this->margin_right = 15;

        $this->margin_top = 45;
        $this->margin_header = 5;

        $this->margin_bottom = 30;
        $this->margin_footer = 15;

        $this->path_images = sfConfig::get('sf_web_dir').'/images/pdf/';
        $this->header_logo = 'logo_'.strtolower(sfConfig::get('sf_app')).'.jpg';
        $this->header_logo_width = 40;

    }
}
