<?php

class ExportDRevPDF extends ExportPDF {

    protected $drev = null;

    public function __construct($drev, $type = 'pdf', $use_cache = false, $file_dir = null, $filename = null) {
        $this->drev = $drev;
        if (!$filename) {
            $filename = $this->getFileName(true, true);
        }

        parent::__construct($type, $use_cache, $file_dir, $filename);
    }

    public function create() {
        $this->printable_document->addPage($this->getPartial('drev/pdf', array('drev' => $this->drev)));
        if(!is_null($this->drev->getProduitsCepageByAppellations())) {
            $this->printable_document->addPage($this->getPartial('drev/pdfCepages', array('drev' => $this->drev)));
        }
        $this->printable_document->addPage($this->getPartial('drev/pdfLots', array('drev' => $this->drev)));
    }

    protected function getHeaderTitle() {
        return sprintf("Déclaration de Revendication %s", $this->drev->campagne);
    }

    protected function getHeaderSubtitle() {

        $header_subtitle = sprintf("%s\n\n", $this->drev->declarant->nom
        );

        if (!$this->drev->isPapier() && $this->drev->validation && $this->drev->validation !== true) {
            $date = new DateTime($this->drev->validation);
            $header_subtitle .= sprintf("Signé électroniquement via l'application de télédéclaration le %s", $date->format('d/m/Y'));
        } else {
            $header_subtitle .= sprintf("Exemplaire brouillon");
        }

        if ($this->drev->isPapier() && $this->drev->validation && $this->drev->validation !== true) {
            $date = new DateTime($this->drev->validation);
            $header_subtitle .= sprintf("Reçue le %s", $date->format('d/m/Y'));
        }  

        return $header_subtitle;
    }

    protected function getConfig() {

        return new ExportDRevPDFConfig();
    }

    public function getFileName($with_rev = false) {

        return self::buildFileName($this->drev, true, false);
    }

    public static function buildFileName($drev, $with_rev = false) {
        $filename = sprintf("DREV_%s_%s", $drev->identifiant, $drev->campagne);

        $declarant_nom = strtoupper(KeyInflector::slugify($drev->declarant->nom));
        $filename .= '_' . $declarant_nom;

        if ($with_rev) {
            $filename .= '_' . $drev->_rev;
        }

        return $filename . '.pdf';
    }

}
