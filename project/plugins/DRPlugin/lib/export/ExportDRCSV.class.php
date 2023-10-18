<?php

class ExportDRCSV extends ExportDouaneCSV
{
    public function export() {
        $csv = parent::export();
        if (count($this->doc->getDonnees()) >= 1) {
          $c = new DRDouaneCsvFile(null, $this->doc, $this->drev_produit_filter);
        	$csv .= $c->convertByDonnees();
        } elseif ($file = $this->doc->getFichier('csv')) {
        	$c = new DRDouaneCsvFile($file, $this->doc, $this->drev_produit_filter);
        	$csv .= $c->convert();
        }
        return $csv;
    }
}
