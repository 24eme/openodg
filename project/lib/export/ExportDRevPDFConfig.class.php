<?php

class ExportDRevPDFConfig extends ExportPDFConfig
{
    public function __construct() {
        parent::__construct();
        $this->subject = 'Declaration de Revendication';
        $this->keywords = 'AVA, Teledeclaration, DRev, Revendication, Declaration';
    }
}