<?php

class ExportDegustationCourrierPDF extends ExportDeclarationLotsPDF
{
    protected $degustation = null;
    protected $lot = null;
    protected $etablissement = null;
    protected $courrier = null;

    public function __construct($courrier, $type = 'pdf', $use_cache = false, $file_dir = null, $filename = null)
    {
        if (!$courrier) {
            throw new sfException('Courrier cannot be null');
        }
        $this->courrier = $courrier;
        $this->degustation = $courrier->getDegustation();
        $this->lot = $courrier->getLot();
        $this->etablissement = $this->lot->getEtablissement();

        parent::__construct($this->degustation, $type, $use_cache, $file_dir, $filename);
    }

    public function getConfig() {
        return new ExportCourrierPDFConfig();
    }

    public function create()
    {
        for($i = 0 ; $i < $this->courrier->getNbPages() ; $i++) {
            $this->printable_document->addPage(
                $this->getPartial('degustation/'.$this->courrier->getPDFTemplateNameForPageId($i), [
                    'degustation' => $this->degustation,
                    'etablissement' => $this->etablissement,
                    'courrier' => $this->courrier,
                    'lot' => $this->lot
                ])
            );
        }
    }

    protected function getHeaderTitle()
    {
        return $this->courrier->courrier_titre;
    }

    protected function getHeaderSubtitle() {
        $header_subtitle = sprintf("%s\n\n", $this->etablissement->nom);
        $header_subtitle .= sprintf("%s", $this->courrier->getDateFormat('d/m/Y'));
        return $header_subtitle;
    }

    public function getFileName($with_rev = false) {
        $filename = sprintf("%s_%s", $this->courrier->courrier_type, $this->lot->unique_id);
        if ($with_rev) {
            $filename .= '_' . $this->degustation->_id . '_' . $this->degustation->_rev;
        }

        return $filename . '.pdf';
    }
}
