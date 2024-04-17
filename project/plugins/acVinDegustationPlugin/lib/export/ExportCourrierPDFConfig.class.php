<?php

class ExportCourrierPDFConfig extends ExportPDFConfig
{
    public function __construct() {
        parent::__construct();
        $this->header_enabled = false;
        $this->margin_top = 5;
        $this->margin_bottom += 5;
    }
}
