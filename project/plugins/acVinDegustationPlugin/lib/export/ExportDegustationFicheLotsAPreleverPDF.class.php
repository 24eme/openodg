<?php

class ExportDegustationFicheLotsAPreleverPDF extends ExportDeclarationLotsPDF {

    protected $degustation = null;

    public function __construct($degustation, $type = 'pdf', $use_cache = false, $file_dir = null, $filename = null) {
        $this->degustation = $degustation;

        parent::__construct($degustation,$type, $use_cache, $file_dir, $filename);
    }

    public function create() {
      $etablissements = array();
      $lots = array();
      foreach ($this->degustation->getLotsPrelevables() as $lot) {
          if(!isset($etablissements[$lot->declarant_identifiant])) {
              $etablissements[$lot->declarant_identifiant] = EtablissementClient::getInstance()->findByIdentifiant($lot->declarant_identifiant);
          }
          $lots[$etablissements[$lot->declarant_identifiant]->code_postal.'/'.$lot->declarant_identifiant][$lot->getNumeroDossier()][] = $lot;
      }

      ksort($lots);

      @$this->printable_document->addPage(
        $this->getPartial('degustation/ficheLotsAPrelevesPdf',
          array(
            'degustation' => $this->degustation,
            'etablissements' => $etablissements,
            "date_edition" => date("d/m/Y"),
            "nbLotTotal" => count($this->degustation->getLotsPrelevables()),
            'lots' => $lots
          )
        ));
    }


    protected function getHeaderTitle() {
        return "Fiche de tournée";
    }

    protected function getHeaderSubtitle() {
        $header_subtitle = sprintf("(Liste des lots à prélever)\nDégustation du %s", $this->degustation->getDateFormat('d/m/Y'));
        $header_subtitle .= sprintf("\nLieu de dégustation: %s", $this->degustation->lieu);
        return $header_subtitle;
    }


    public function getFileName($with_rev = false) {
        $filename = sprintf("fiche_tournee_prelevements_%s", $this->degustation->_id);


        if ($with_rev) {
            $filename .= '_' . $this->degustation->_rev;
        }


        return $filename . '.pdf';
    }

}
