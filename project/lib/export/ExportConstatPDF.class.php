<?php

class ExportConstatPDF extends ExportPDF {

    protected $constats = null;
    protected $constatNode = null;

    public function __construct($constats,$constatNode, $type = 'pdf', $use_cache = false, $file_dir = null, $filename = null) {
        $this->constats = $constats;
        $this->constatNode = $constatNode;
        if (!$filename) {
            $filename = $this->getFileName(true, true);
        }

        parent::__construct($type, $use_cache, $file_dir, $filename);
    }

    public function create() {
        $this->printable_document->addPage($this->getPartial('constats/pdf', array('constats' => $this->constats,'constatNode' => $this->constatNode)));
      
    }

    protected function getHeaderTitle() {
        return sprintf("Constat %s %s %s", $this->constats->identifiant, $this->constats->campagne,$this->constatNode);
    }

    protected function getHeaderSubtitle() {

        $header_subtitle = sprintf("%s\n\n", $this->constats->raison_sociale);
        
        return $header_subtitle;
    }

    protected function getConfig() {

        return new ExportDRevPDFConfig();
    }

    public function getFileName($with_rev = false) {

        return self::buildFileName($this->constats,$this->constatNode, true, false);
    }

    public static function buildFileName($constats, $constatNode, $with_rev = false) {
        $filename = sprintf("DREV_%s_%s_%s", $constats->identifiant, $constats->campagne,$constatNode);

        $declarant_nom = strtoupper(KeyInflector::slugify($constats->raison_sociale));
        $filename .= '_' . $declarant_nom;

        if ($with_rev) {
            $filename .= '_' . $constats->_rev;
        }

        return $filename . '.pdf';
    }

}
