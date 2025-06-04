<?php

class ExportDegustationFicheLotsAPreleverPDFConfig extends ExportPDFConfig
{
    public function __construct() {
        parent::__construct();
        $this->subject = 'Fiche des tournées';
        $this->orientation = self::ORIENTATION_LANDSCAPE;
        $this->keywords = 'Degustation';
    }
}
