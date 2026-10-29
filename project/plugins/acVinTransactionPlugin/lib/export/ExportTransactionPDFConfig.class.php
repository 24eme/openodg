<?php

class ExportTransactionPDFConfig extends ExportPDFConfig
{
    public function __construct() {
        parent::__construct();
        $this->subject = 'Declaration de Transaction';
        $this->orientation = self::ORIENTATION_LANDSCAPE;
        $this->keywords = 'Teledeclaration, Transaction, Declaration';
    }
}
