<?php

class ExportPMCPDFConfig extends ExportPDFConfig
{
    public function __construct() {
        parent::__construct();
        $this->subject = 'Declaration de Mise en Circulation';
        $this->orientation = self::ORIENTATION_PORTRAIT;
        $this->keywords = 'Teledeclaration, Mise en circulation, Declaration';
    }
}
