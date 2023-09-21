<?php

class ExportDegustationRapportInspectionPDF extends ExportDeclarationLotsPDF
{
    protected $degustation = null;
    protected $lot = null;
    protected $etablissement = null;

    public function __construct($degustation, $lot, $type = 'pdf', $use_cache = false, $file_dir = null, $filename = null)
    {
        $this->degustation = $degustation;
        $this->lot = $lot;
        $this->etablissement = $lot->getEtablissement();

        parent::__construct($degustation, $type, $use_cache, $file_dir, $filename);
    }

    public function create()
    {
        $this->printable_document->addPage(
            $this->getPartial('degustation/degustationRapportInspection', [
                'degustation' => $this->degustation,
                'etablissement' => $this->etablissement,
                'lot' => $this->lot
            ])
        );
    }

    protected function getHeaderTitle()
    {
        return "Rapport d'inspection";
    }

    protected function getHeaderSubtitle() {
        $header_subtitle = sprintf("%s\n\n", $this->etablissement->nom);
        $header_subtitle .= sprintf("Dégustation du %s", $this->degustation->getDateFormat('d/m/Y'));
        return $header_subtitle;
    }

    public function getFileName($with_rev = false) {
        $filename = sprintf("RAPPORT_INSPECTION_%s", $this->lot->unique_id);
        if ($with_rev) {
            $filename .= '_' . $this->degustation->_id . '_' . $this->degustation->_rev;
        }

        return $filename . '.pdf';
    }
}
