<?php

class ExportTiragePDF extends ExportPDF {

    protected $tirage = null;

    public function __construct($tirage, $type = 'pdf', $use_cache = false, $file_dir = null, $filename = null) {
        $this->tirage = $tirage;
        if (!$filename) {
            $filename = $this->getFileName(true, true);
        }

        parent::__construct($type, $use_cache, $file_dir, $filename);
    }

    public function create() {
        $this->printable_document->addPage($this->getPartial('tirage/pdf', array('tirage' => $this->tirage)));
    }

    protected function getHeaderTitle() {
        return sprintf("Déclaration de Revendication / Déclaration de Tirage du millésime %s", $this->tirage->campagne, $this->tirage->campagne);
    }

    protected function getHeaderSubtitle() {

        $header_subtitle = sprintf("A.O.C. Crémant d'Alsace\n%s\n", $this->tirage->declarant->nom);

        if (!$this->tirage->isPapier() && $this->tirage->validation && $this->tirage->validation !== true) {
            $date = new DateTime($this->tirage->validation);
            $header_subtitle .= sprintf("Signé électroniquement via l'application de télédéclaration le %s", $date->format('d/m/Y'));
        } elseif(!$this->tirage->isPapier()) {
            $header_subtitle .= sprintf("Exemplaire brouillon");
        }

        if ($this->tirage->isPapier() && $this->tirage->validation && $this->tirage->validation !== true) {
            $date = new DateTime($this->tirage->validation);
            $header_subtitle .= sprintf("Reçue le %s", $date->format('d/m/Y'));
        }  

        return $header_subtitle;
    }

    protected function getConfig() {

        return new ExportPDFConfig();
    }

    public function getFileName($with_rev = false) {

        return self::buildFileName($this->tirage, true, false);
    }

    public static function buildFileName($tirage, $with_rev = false) {
        $filename = sprintf("TIRAGE_%s_%s", $tirage->identifiant, $tirage->campagne);

        $declarant_nom = strtoupper(KeyInflector::slugify($tirage->declarant->nom));
        $filename .= '_' . $declarant_nom;

        if ($with_rev) {
            $filename .= '_' . $tirage->_rev;
        }

        return $filename . '.pdf';
    }

}
