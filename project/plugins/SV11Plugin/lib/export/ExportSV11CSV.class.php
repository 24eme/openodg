<?php

class ExportSV11CSV extends ExportDouaneCSV
{
    public function export() {
        $csv = parent::export();
        if ($this->doc->exist('donnees') && count($this->doc->donnees) >= 1) {
            $c = new SV11DouaneCsvFile(null, $this->doc, $this->drev_produit_filter);
            $csv .= $c->convertByDonnees();
        } elseif ($file = $this->doc->getFichier('csv')) {
        	$c = new SV11DouaneCsvFile($file, $this->doc, $this->drev_produit_filter);
        	$csv .= $c->convert();
        }
        return $csv;
    }
}
