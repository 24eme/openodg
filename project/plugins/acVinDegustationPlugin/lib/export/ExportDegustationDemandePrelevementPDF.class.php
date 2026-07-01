<?php

class ExportDegustationDemandePrelevementPDF extends ExportDeclarationLotsPDF {

    protected $degustation = null;
    protected $etablissement = null;

    public function __construct($degustation,$etablissement, $type = 'pdf', $use_cache = false, $file_dir = null, $filename = null) {
        $this->degustation = $degustation;
        $this->etablissement = $etablissement;

        parent::__construct($degustation,$type, $use_cache, $file_dir, $filename);
    }

    public function create() {
        $lots = array();
        $lots = $this->degustation->getLotsByOperateursAndActivite();
        foreach ($lots as $activite => &$operateurs) {
            foreach ($operateurs as &$lotsOperateur) {
                usort($lotsOperateur, function($a, $b) {
                    $typeCompare = strcmp($a->destination_type, $b->destination_type);
                    if ($typeCompare !== 0) {
                        return $typeCompare;
                    }
                    return strcmp($a->destination_date, $b->destination_date);
                });
            }
        }

        $footer = sprintf($this->degustation->getNomOrganisme()." — %s", $this->degustation->getLieuNom());
        $this->printable_document->addPage($this->getPartial('degustation/demandePrelevementPDF', array("footer" => $footer, 'degustation' => $this->degustation, 'etablissement' => $this->etablissement, 'lots' => $lots)));
    }

    protected function getHeaderTitle() {
        return "";
    }

    protected function getHeaderSubtitle() {
        return "";
    }

    public function getFileName($with_rev = false) {
        $filename = sprintf("DEMANDE_PRELEVEMENT_%s", $this->degustation->_id);
        if ($with_rev) {
            $filename .= '_' . $this->degustation->_rev;
        }

        return $filename . '.pdf';
    }

    protected function getFooterText() {
        return sprintf("<br/>%s     %s - %s - %s<br/>%s    %s", Organisme::getInstance(null, 'degustation')->getNom(), Organisme::getInstance(null, 'degustation')->getAdresse(), Organisme::getInstance(null, 'degustation')->getCodePostal(), Organisme::getInstance(null, 'degustation')->getCommune(), Organisme::getInstance(null, 'degustation')->getTelephone(), Organisme::getInstance(null, 'degustation')->getEmail());
    }

}
