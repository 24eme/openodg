<?php

class ExportConstatPDF extends ExportPDF {

    protected $constats = null;
    protected $constatNode = null;

    public function __construct($constats,$constatNode, $type = 'pdf', $use_cache = false, $file_dir = null, $filename = null) {
        $this->constats = $constats;
        $this->constatNode = $constatNode;
        sfContext::getInstance()->getConfiguration()->loadHelpers(array('Date'));
        if (!$filename) {
            $filename = $this->getFileName(true, true);
        }

        parent::__construct($type, $use_cache, $file_dir, $filename);
    }

    public function create() {
        $this->printable_document->addPage($this->getPartial('constats/pdf', array('constats' => $this->constats,'constat' => $this->constats->constats->get($this->constatNode))));
      
    }

    protected function getHeaderTitle() {
        return sprintf("Constat de %s du %s", $this->constats->raison_sociale, format_date(substr($this->constatNode, 0, 4) . '-' . substr($this->constatNode, 4, 2) . '-' . substr($this->constatNode, 6)));
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
        $filename = sprintf("CONSTATS_%s_%s_%s", $constats->identifiant, $constats->campagne,$constatNode);

        $declarant_nom = strtoupper(KeyInflector::slugify($constats->raison_sociale));
        $filename .= '_' . $declarant_nom;

        if ($with_rev) {
            $filename .= '_' . $constats->_rev;
        }

        return $filename . '.pdf';
    }

}
