<?php

class ExportControleManquementPDFConfig extends ExportPDFConfig
{
    public function __construct() {
        parent::__construct();
        $this->subject = 'manquement';
        $this->orientation = self::ORIENTATION_PORTRAIT;
        $this->keywords = 'Controle';
        $this->creator = 'ODG';
        $this->author = 'ODG';

        $this->font_name = 'helvetica';
        $this->margin_left = 5;
        $this->margin_top = 5;
        $this->margin_right = 5;
        $this->margin_bottom = 5;

        $this->font_size = 10;
        $this->font_size_main = 10;
        $this->header_enabled = false;

        $this->path_images = sfConfig::get('sf_web_dir').'/images/pdf/';
        $this->header_logo = 'logo_'.sfConfig::get('sf_app').'.jpg';
        $this->header_logo_width = 20;
    }
}
