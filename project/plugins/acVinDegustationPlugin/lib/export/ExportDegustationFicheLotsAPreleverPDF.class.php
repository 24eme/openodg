<?php

class ExportDegustationFicheLotsAPreleverPDF extends ExportDeclarationLotsPDF {

    protected $degustation = null;

    public function __construct($degustation, $type = 'pdf', $use_cache = false, $file_dir = null, $filename = null) {
        $this->degustation = $degustation;

        parent::__construct($degustation,$type, $use_cache, $file_dir, $filename);
    }

    public function create() {
      $etablissements = array();
      $adresses = array();
      foreach ($this->degustation->getLotsPrelevables() as $lot) {
          $adresses[$lot->declarant_identifiant][$lot->getNumeroDossier()] = $lot;
          $etablissements[$lot->declarant_identifiant] = EtablissementClient::getInstance()->findByIdentifiant($lot->declarant_identifiant);

      }

      @$this->printable_document->addPage(
        $this->getPartial('degustation/ficheLotsAPrelevesPdf',
          array(
            'degustation' => $this->degustation,
            'etablissements' => $etablissements,
            "date_edition" => date("d/m/Y"),
            "nbLotTotal" => count($this->degustation->getLots()),
            'lots' => $adresses
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
