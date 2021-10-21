<?php

class ExportSV12CSV extends ExportDouaneCSV
{
    public function export() {
        $csv = parent::export();
        if ($this->doc->exist('donnees') && count($this->doc->donnees) >= 1) {
            $c = new SV12DouaneCsvFile(null, $this->doc);
            $csv .= $c->convertByDonnees($this->extraFields);
        } elseif ($file = $this->doc->getFichier('csv')) {
        	$c = new SV12DouaneCsvFile($file, $this->doc);
        	$csv .= $c->convert($this->extraFields);
        }
        return $csv;
    }
}
