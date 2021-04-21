<?php

class ExportConditionnementPDFConfig extends ExportPDFConfig
{
    public function __construct() {
        parent::__construct();
        $this->subject = 'Declaration de Conditionnement';
        $this->orientation = self::ORIENTATION_LANDSCAPE;
        $this->keywords = 'Teledeclaration, Conditionnement, Declaration';
    }
}
