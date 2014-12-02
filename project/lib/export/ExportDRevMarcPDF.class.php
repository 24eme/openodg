<?php

class ExportDRevMarcPDF extends ExportPDF {

    protected $drevmarc = null;

    public function __construct($drevmarc, $type = 'pdf', $use_cache = false, $file_dir = null,  $filename = null) {
        $this->drevmarc = $drevmarc;
        if(!$filename) {
            $filename = $this->getFileName(true, true);
        }

        parent::__construct($type, $use_cache, $file_dir, $filename);
    }

    public function create() {
        $this->printable_document->addPage($this->getPartial('drevmarc/pdf', array('drevmarc' => $this->drevmarc)));
    }

    protected function getHeaderTitle() {
        return sprintf("Déclaration de Revendication de Marc d'Alsace Gewurztraminer %s", $this->drevmarc->campagne);
    }

    protected function getHeaderSubtitle() {
        $header_subtitle = sprintf("%s\n\n", $this->drevmarc->declarant->nom);
        if (!$this->drevmarc->isPapier() && $this->drevmarc->validation && $this->drevmarc->campagne >= "2014") {
            $date = new DateTime($this->drevmarc->validation);
            $header_subtitle .= sprintf("Signé électroniquement via l'application de télédéclaration le %s", $date->format('d/m/Y'));
        }

        if ($this->drevmarc->isPapier() && $this->drevmarc->validation && $this->drevmarc->validation !== true) {
            $date = new DateTime($this->drevmarc->validation);
            $header_subtitle .= sprintf("Reçue le %s", $date->format('d/m/Y'));
        } 

        return $header_subtitle;        
    }

    protected function getConfig() {

        return new ExportDRevPDFConfig();
    }

    public function getFileName($with_rev = false) {

      return self::buildFileName($this->drevmarc, true, false);
    }

    public static function buildFileName($drevmarc, $with_rev = false) {
        
        $filename = sprintf("DREVMARC_%s_%s", $drevmarc->identifiant, $drevmarc->campagne);

        $declarant_nom = strtoupper(KeyInflector::slugify($drevmarc->declarant->nom));
        $filename .= '_' . $declarant_nom;

        if ($with_rev) {
            $filename .= '_' . $drevmarc->_rev;
        }

        return $filename . '.pdf';
    }
}