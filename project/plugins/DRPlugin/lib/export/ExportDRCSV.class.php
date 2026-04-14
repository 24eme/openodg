<?php

class ExportDRCSV extends ExportDouaneCSV
{
    public function export() {
        $csv = parent::export();
        if ($this->doc->exist('donnees') && count($this->doc->getDonnees()) >= 1) {
          $c = new DRDouaneCsvFile(null, $this->doc, $this->drev_produit_filter);
          $csv .= $c->convertByDonnees();
        } elseif ($file = $this->doc->getFichier('json')) {
            $c = new DRDouaneJsonFile($file, $this->doc, $this->drev_produit_filter);
            $csv .= $c->convert();
        } elseif ($file = $this->doc->getFichier('csv')) {
            $c = new DRDouaneCsvFile($file, $this->doc, $this->drev_produit_filter);
            $csv .= $c->convert();
        }
        if ($this->doc->isBailleur()) {
            $e = EtablissementClient::getInstance()->findByIdentifiant($this->doc->identifiant);
            $docs_metayers = DRClient::getInstance()->getDocumentsDouaniers($e, $this->doc->campagne);
            foreach ($docs_metayers as $doc) {
                if (count($doc->getDonnees()) >= 1) {
                  $c = new DRDouaneCsvFile(null, $doc, $this->drev_produit_filter);
                  $csv .= $c->convertByDonnees();
                } elseif ($file = $doc->getFichier('json')) {
                    $c = new DRDouaneJsonFile($file, $doc, $this->drev_produit_filter);
                    $csv .= $c->convert();
                } elseif ($file = $doc->getFichier('csv')) {
                    $c = new DRDouaneCsvFile($file, $doc, $this->drev_produit_filter);
                    $csv .= $c->convert();
                }
            }
        }
        return $csv;
    }
}
