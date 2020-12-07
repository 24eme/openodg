<?php

class ExportDegustationFichePresenceDegustateursPDFConfig extends ExportPDFConfig
{
    public function __construct() {
        parent::__construct();
        $this->subject = 'Feuille de présence';
        $this->orientation = self::ORIENTATION_PORTRAIT;
        $this->keywords = 'Degustation';
    }
}
