<?php

class ExportDRCSV extends ExportDouaneCSV
{
    public function export() {
        $csv = parent::export();
        if ($this->doc->exist('donnees') && count($this->doc->donnees) >= 1) {
          $c = new DRDouaneCsvFile(null, $this->doc);
        	$csv .= $c->convertByDonnees();
        } elseif ($file = $this->doc->getFichier('csv')) {
        	$c = new DRDouaneCsvFile($file, $this->doc);
        	$csv .= $c->convert();
        }
        return $csv;
    }
}
