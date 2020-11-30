<?php

class ExportDegustationFicheEchantillonsPrelevesPDFConf extends ExportPDFConfig
{
    public function __construct() {
        parent::__construct();
        $this->subject = 'Fiche Échantillons à Prélévés';
        $this->orientation = self::ORIENTATION_PORTRAIT;
        $this->keywords = 'Degustation';
    }
}
