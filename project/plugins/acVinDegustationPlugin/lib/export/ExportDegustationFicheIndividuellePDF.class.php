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
        sfApplicationConfiguration::getActive()->loadHelpers(array('Partial'));
        try {
            return get_partial('degustation/ficheIndividuellePdfHeader', ['degustation' => $this->degustation]);
        } catch (Exception $e) {
            return "Fiche individuelle de dégustation";
        }
    }

    protected function getHeaderSubtitle()
    {
        sfApplicationConfiguration::getActive()->loadHelpers(array('Partial'));
        try {
            return get_partial('degustation/ficheIndividuellePdfHeaderSubtitle', ['degustation' => $this->degustation]);
        } catch (Exception $e) {
            $header_subtitle = sprintf("\nDégustation du %s", $this->degustation->getDateFormat('d/m/Y'));
            $header_subtitle .= sprintf("\n%s", $this->degustation->lieu);
            return $header_subtitle;
        }
    }


    protected function getFooterText() {
        return sprintf("<br/>%s     %s - %s - %s<br/>%s    %s", Organisme::getInstance(null, 'degustation')->getNom(), Organisme::getInstance(null, 'degustation')->getAdresse(), Organisme::getInstance(null, 'degustation')->getCodePostal(), Organisme::getInstance(null, 'degustation')->getCommune(), Organisme::getInstance(null, 'degustation')->getTelephone(), Organisme::getInstance(null, 'degustation')->getEmail());
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
