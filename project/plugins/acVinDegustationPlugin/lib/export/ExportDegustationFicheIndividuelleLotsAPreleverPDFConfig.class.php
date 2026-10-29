<?php

class ExportDegustationFicheIndividuelleLotsAPreleverPDFConfig extends ExportPDFConfig
{
    public function __construct() {
        parent::__construct();
        $this->subject = 'Fiche Individuelle des Lots à Prélever';
        $this->orientation = self::ORIENTATION_LANDSCAPE;
        $this->keywords = 'Degustation';

        
    }
}
