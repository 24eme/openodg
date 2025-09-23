<?php

class ExportDegustationNonConformitePDF extends ExportDeclarationLotsPDF {

    protected $degustation = null;
    protected $lot = null;
    protected $etablissement = null;

    public function __construct($degustation, $lot, $type = 'pdf', $use_cache = false, $file_dir = null, $filename = null) {
        $this->degustation = $degustation;
        $this->lot = $lot;
        $this->etablissement = $lot->getEtablissement();

        parent::__construct($degustation,$type, $use_cache, $file_dir, $filename);
    }

    public function create() {
        if ($this->lot->conformite === Lot::CONFORMITE_NONTYPICITE_CEPAGE) {
            $this->printable_document->addPage($this->getPartial('degustation/degustationNonConformitePDF_typiciteCepage_page1', array('degustation' => $this->degustation, 'etablissement' => $this->etablissement, 'lot' => $this->lot)));
            $this->printable_document->addPage($this->getPartial('degustation/degustationNonConformitePDF_typiciteCepage_page2', array('degustation' => $this->degustation, 'etablissement' => $this->etablissement, 'lot' => $this->lot)));
        } elseif ($this->lot->conformite === Lot::CONFORMITE_NONCONFORME_ANALYTIQUE) {
            $this->printable_document->addPage($this->getPartial('degustation/degustationNonConformitePDF_analytique', array('degustation' => $this->degustation, 'etablissement' => $this->etablissement, 'lot' => $this->lot)));
        } else {
            $this->printable_document->addPage($this->getPartial('degustation/degustationNonConformitePDF_page1', array('degustation' => $this->degustation, 'etablissement' => $this->etablissement, "lot" => $this->lot)));
            $this->printable_document->addPage($this->getPartial('degustation/degustationNonConformitePDF_page2', array('degustation' => $this->degustation, 'etablissement' => $this->etablissement, "lot" => $this->lot )));
        }
    }

    protected function getHeaderTitle() {
        if (DegustationConfiguration::getInstance()->hasAcceptabiliteAoc($this->degustation->getRegion())) {
            return "Résultat de lot non acceptable";
        } else {
            return "Résultat de lot non conforme";
        }
    }

    protected function getHeaderSubtitle() {
        $header_subtitle = sprintf("%s\n\n", $this->etablissement->nom);
        $header_subtitle .= sprintf("Dégustation du %s", $this->degustation->getDateFormat('d/m/Y'));
        return $header_subtitle;
    }

    public function getFileName($with_rev = false) {
        $filename = sprintf("NON_CONFORMITE_%s", $this->degustation->_id);
        if ($with_rev) {
            $filename .= '_' . $this->degustation->_rev;
        }

        return $filename . '.pdf';
    }

}
