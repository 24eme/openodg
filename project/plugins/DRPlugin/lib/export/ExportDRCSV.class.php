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
        if ($this->doc->exist('has_metayers')) {
            $docs_metayers = DRClient::getInstance()->getDocumentsDouaniers($this->doc->identifiant, $this->doc->campagne);
            foreach ($docs_metayers as $doc) {
                if (count($doc->getDonnees()) >= 1) {
                  $c = new DRDouaneCsvFile(null, $doc, $this->drev_produit_filter);
                  $csv .= $c->convertByDonnees($this->doc->declarant->ppm, true);
                } elseif ($file = $doc->getFichier('csv')) {
                	$c = new DRDouaneCsvFile($file, $doc, $this->drev_produit_filter);
                	$csv .= $c->convert();
                }
            }
        }
        return $csv;
    }
}
