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
        $dateValidation = null;
        if($this->drev->getDateDepot()) {
            $dateValidation = new DateTime($this->drev->getDateDepot());
        }
        $dateOdg = null;
        if($this->drev->validation_odg) {
            $dateOdg = new DateTime($this->drev->validation_odg);
        }

        if (!$this->drev->isPapier() && $dateValidation) {
            $header_subtitle .= sprintf("Signé électroniquement via la télédéclaration le %s, %s", $dateValidation->format('d/m/Y'), ($dateOdg) ? "validée par l'ODG le ".$dateOdg->format('d/m/Y') : "en attente de l'approbation par l'ODG");
        } elseif(!$this->drev->isPapier()) {
            $header_subtitle .= sprintf("Exemplaire brouillon");
        }

        if ($this->drev->isPapier() && $this->drev->getDateDepot()) {
            $date = new DateTime($this->drev->getDateDepot());
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
