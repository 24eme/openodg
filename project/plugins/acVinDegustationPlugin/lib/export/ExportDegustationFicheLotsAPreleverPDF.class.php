<?php

class ExportDegustationFicheLotsAPreleverPDF extends ExportDeclarationLotsPDF {

    protected $degustation = null;

    public function __construct($degustation, $type = 'pdf', $use_cache = false, $file_dir = null, $filename = null) {
        $this->degustation = $degustation;

        parent::__construct($degustation,$type, $use_cache, $file_dir, $filename);
    }

    public function create() {
      $etablissements = array();
      foreach ($this->degustation->getLotsByNumDossierNumArchive() as $numDossier => $lotsEtablissement) {
        if (!$numDossier) {
            continue;
        }
	    $etablissement = EtablissementClient::getInstance()->findByIdentifiant($lotsEtablissement[array_key_first($lotsEtablissement)]->declarant_identifiant);
        $etablissements[$numDossier] = $etablissement;
      }
        $lots = $this->degustation->getLotsByNumDossierNumArchive();
        @$this->printable_document->addPage(
          $this->getPartial('degustation/ficheLotsAPrelevesPdf',
          array(
            'degustation' => $this->degustation,
            'etablissements' => $etablissements,
            "date_edition" => date("d/m/Y"),
            "nbLotTotal" => count($this->degustation->getLots()),
            'lots' => $lots
          )
        ));
    }



    protected function getHeaderTitle() {
        return "Fiche de tournée";
    }

    protected function getHeaderSubtitle() {
        $header_subtitle = sprintf("\n%s\nFiche de tournée (Liste des lots à prélever)\nDate de commission : %s", $this->degustation->lieu,$this->degustation->getDateFormat('d/m/Y'));

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
