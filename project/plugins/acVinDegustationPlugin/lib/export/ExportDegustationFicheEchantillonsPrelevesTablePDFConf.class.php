<?php

class ExportDegustationFicheEchantillonsPrelevesTablePDFConf extends ExportPDFConfig
{
    public function __construct() {
        parent::__construct();
        $this->subject = 'Fiche Échantillons Prélévés par Table';
        $this->orientation = self::ORIENTATION_LANDSCAPE;
        $this->keywords = 'Degustation';

        $this->font_name = 'helvetica';
        $this->margin_left = 5;
        $this->margin_right = 5;

    }
}
