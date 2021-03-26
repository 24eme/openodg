<?php

class ExportDegustationFichePresenceDegustateursPDFConfig extends ExportPDFConfig
{
    public function __construct() {
        parent::__construct();
        $this->subject = 'Feuille de présence';
        $this->orientation = self::ORIENTATION_PORTRAIT;
        $this->keywords = 'Degustation';

        $this->margin_left = 15;
        $this->margin_top = 30;
        $this->margin_right = 15;
        $this->margin_bottom = 25;
        $this->margin_header = 5;
        $this->margin_footer = 20;
        $this->font_size = 12;
        $this->font_size_main = 12;
    }
}
