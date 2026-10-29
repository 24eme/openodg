<?php

class ExportDegustationFicheProcesVerbalDegustationPDFConfig extends ExportPDFConfig
{
    public function __construct() {
        parent::__construct();
        $this->subject = 'Procès verbal global';
        $this->orientation = self::ORIENTATION_LANDSCAPE;
        $this->keywords = 'Degustation';
        $this->creator = 'IGP';
        $this->author = 'IGP';

        $this->font_name = 'helvetica';
        $this->margin_left = 2;
        $this->margin_right = 2;
        $this->font_size = 8;
        $this->font_size_main = 8;
    }
}
