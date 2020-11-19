<?php

class ExportDegustationFicheIndividuellePDFConfig extends ExportPDFConfig
{
    public function __construct() {
        parent::__construct();
        $this->subject = 'Fiche Individuelle';
        $this->orientation = self::ORIENTATION_LANDSCAPE;
        $this->keywords = 'Degustation';
    }
}
