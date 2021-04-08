<?php

class ExportDegustationFicheIndividuellePDF extends ExportPDF {

    protected $degustation = null;

    public function __construct($degustation, $type = 'pdf', $use_cache = false, $file_dir = null, $filename = null) {
        $this->degustation = $degustation;

        if (!$filename) {
            $filename = $this->getFileName(true);
        }
        parent::__construct($type, $use_cache, $file_dir, $filename);
    }

    public function create() {
        $notation = DegustationConfiguration::getInstance()->hasNotation();

        if ($this->degustation->getLastNumeroTable() < 1) {
            throw new sfException('Pas de lots attablés : '.$this->degustation->_id);
        }

      for($nbtable=1 ;$nbtable <= $this->degustation->getLastNumeroTable(); $nbtable++){
        @$this->printable_document->addPage($this->getPartial('degustation/ficheIndividuellePdf', array('table' => $nbtable, 'degustation' => $this->degustation, 'lots' => $this->degustation->getLotsByTable($nbtable), 'notation' => $notation)));
      }
    }


    public function output() {
        if($this->printable_document instanceof PageableHTML) {
            return parent::output();
        }

        return file_get_contents($this->getFile());
    }

    public function getFile() {

        if($this->printable_document instanceof PageableHTML) {
            return parent::getFile();
        }

        return sfConfig::get('sf_cache_dir').'/pdf/'.$this->getFileName(true);
    }

    protected function getHeaderTitle() {
        $titre = $this->degustation->getNomOrganisme();

        return $titre;
    }

    protected function getHeaderSubtitle() {

        $header_subtitle = sprintf("%s\n\n", $this->degustation->lieu)."FICHE INDIVIDUELLE DE DEGUSTATION";

        return $header_subtitle;
    }


    protected function getFooterText() {
        $footer= sprintf($this->degustation->getNomOrganisme()." — %s", $this->degustation->getLieuNom());
        return $footer;
    }

    protected function getConfig() {

        return new ExportDegustationFicheIndividuellePDFConfig();
    }

    public function getFileName($with_rev = false) {

        return self::buildFileName($this->degustation, true);
    }

    public static function buildFileName($degustation, $with_rev = false) {
        $filename = sprintf("fiche_individuelle_degustation_%s", $degustation->_id);


        if ($with_rev) {
            $filename .= '_' . $degustation->_rev;
        }


        return $filename . '.pdf';
    }

}
