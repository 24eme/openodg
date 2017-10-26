<?php

class ExportTravauxMarcPDF extends ExportPDF {

    protected $travauxmarc = null;

    public function __construct($travauxmarc, $type = 'pdf', $use_cache = false, $file_dir = null,  $filename = null) {
        $this->travauxmarc = $travauxmarc;
        if(!$filename) {
            $filename = $this->getFileName(true, true);
        }

        parent::__construct($type, $use_cache, $file_dir, $filename);
    }

    public function create() {
        $this->printable_document->addPage($this->getPartial('travauxmarc/pdf', array('travauxmarc' => $this->travauxmarc)));
    }

    protected function getHeaderTitle() {
        return sprintf("Déclaration d'ouverture des travaux de distillation %s", $this->travauxmarc->campagne);
    }

    protected function getHeaderSubtitle() {
        $header_subtitle = sprintf("de l'AOC Marc d'Alsace Gewurztraminer\n%s\n", $this->travauxmarc->declarant->nom);
        if (!$this->travauxmarc->isPapier() && $this->travauxmarc->validation && $this->travauxmarc->campagne >= "2014") {
            $date = new DateTime($this->travauxmarc->validation);
            $header_subtitle .= sprintf("Signé électroniquement via l'application de télédéclaration le %s", $date->format('d/m/Y'));
        } elseif(!$this->travauxmarc->isPapier()) {
            $header_subtitle .= sprintf("Exemplaire brouillon");
        }

        if ($this->travauxmarc->isPapier() && $this->travauxmarc->validation && $this->travauxmarc->validation !== true) {
            $date = new DateTime($this->travauxmarc->validation);
            $header_subtitle .= sprintf("Reçue le %s", $date->format('d/m/Y'));
        }

        return $header_subtitle;
    }

    protected function getConfig() {

        return new ExportDRevPDFConfig();
    }

    public function getFileName($with_rev = false) {

      return self::buildFileName($this->travauxmarc, true, false);
    }

    public static function buildFileName($travauxmarc, $with_rev = false) {

        $filename = sprintf("TRAVAUXMARC_%s_%s", $travauxmarc->identifiant, $travauxmarc->campagne);

        $declarant_nom = strtoupper(KeyInflector::slugify($travauxmarc->declarant->nom));
        $filename .= '_' . $declarant_nom;

        if ($with_rev) {
            $filename .= '_' . $travauxmarc->_rev;
        }

        return $filename . '.pdf';
    }
}
