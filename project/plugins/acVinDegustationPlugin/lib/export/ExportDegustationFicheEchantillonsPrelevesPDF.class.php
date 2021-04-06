<?php

class ExportDegustationFicheEchantillonsPrelevesPDF extends ExportDeclarationLotsPDF {

    protected $degustation = null;

    public function __construct($degustation, $type = 'pdf', $use_cache = false, $file_dir = null, $filename = null) {
        $this->degustation = $degustation;
        parent::__construct($degustation, $type, $use_cache, $file_dir, $filename);
    }

    public function create() {
        @$this->printable_document->addPage(
          $this->getPartial('degustation/ficheEchantillonsPrelevesPdf',
          array(
            'degustation' => $this->degustation,
            'lots' => $this->degustation->getLotsByNumDossier()
          )
        ));
    }



    protected function getHeaderTitle() {
       return "Fiche des lots ventilés anonymisés";
    }

    protected function getHeaderSubtitle() {

        $header_subtitle = sprintf("\nDégustation du %s", $this->degustation->getDateFormat('d/m/Y'));
        $header_subtitle .= sprintf("\n%s", $this->degustation->lieu);

        return $header_subtitle;
    }


    public function getFileName($with_rev = false) {
        $filename = sprintf("fiche_echantillons_preleves_%s", $this->degustation->_id);
        if ($with_rev) {
            $filename .= '_' . $this->degustation->_rev;
        }
        return $filename . '.pdf';
    }


}
