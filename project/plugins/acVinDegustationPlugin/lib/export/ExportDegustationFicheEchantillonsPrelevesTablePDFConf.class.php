<?php

class ExportDegustationFicheEchantillonsPrelevesTablePDFConf extends ExportPDFConfig
{
    public function __construct() {
        parent::__construct();
        $this->subject = 'Fiche Échantillons Prélévés par Table';
        $this->orientation = self::ORIENTATION_PORTRAIT;
        $this->keywords = 'Degustation';
    }
}
