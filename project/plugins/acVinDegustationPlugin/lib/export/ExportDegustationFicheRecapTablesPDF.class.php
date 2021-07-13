<?php

class ExportDegustationFicheRecapTablesPDF extends ExportDeclarationLotsPDF {

    protected $degustation = null;

    public function __construct($degustation, $type = 'pdf', $use_cache = false, $file_dir = null, $filename = null) {
        $this->degustation = $degustation;

        parent::__construct($degustation, $type, $use_cache, $file_dir, $filename);
    }

    public function create() {
        $lotsByTable = array();
      foreach ($this->degustation->getLotsSortByTables() as $lot) {
          $lotsByTable[$lot->numero_table][$lot->numero_anonymat] = $lot;
      }

        if (empty($lotsByTable)) {
            throw new sfException('Pas de lots attablés : '.$this->degustation->_id);
        }

        foreach ($lotsByTable as &$table) {
            ksort($table);
        }

      foreach($lotsByTable as $numeroTable => $lots) {
          @$this->printable_document->addPage(
            $this->getPartial('degustation/ficheRecapTablesPdf',
            array(
              'degustation' => $this->degustation,
              'lots' => $lots,
              'numTab' => $numeroTable
            )
          ));
        }
    }


    protected function getHeaderTitle() {
        return "Fiche de synthèse";
    }

    protected function getHeaderSubtitle() {
        $header_subtitle = sprintf("\nDégustation du %s", $this->degustation->getDateFormat('d/m/Y'));
        $header_subtitle .= sprintf("\n%s", $this->degustation->lieu);

        return $header_subtitle;
    }


    public function getFileName($with_rev = false) {
        $filename = sprintf("Fiche_synthese_recap_tables_%s", $this->degustation->_id);
        if ($with_rev) {
            $filename .= '_' . $this->degustation->_rev;
        }

        return $filename . '.pdf';
    }

}
