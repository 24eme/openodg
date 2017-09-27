<?php

class ExportDRevPDFConfig extends ExportPDFConfig
{
    public function __construct() {
        parent::__construct();
        $this->subject = 'Declaration de Revendication';
        $this->orientation = self::ORIENTATION_LANDSCAPE;
        $this->keywords = 'Teledeclaration, DRev, Revendication, Declaration';
    }
}
