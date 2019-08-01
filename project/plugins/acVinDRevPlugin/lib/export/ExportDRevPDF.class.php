<?php

class ExportDRevPDF extends ExportPDF {

    protected $drev = null;
    protected $regions = array();

    public function __construct($drev, $region = null, $type = 'pdf', $use_cache = false, $file_dir = null, $filename = null) {
        $this->drev = $drev;
        if(!$region && DrevConfiguration::getInstance()->hasOdgProduits()) {
            foreach (DrevConfiguration::getInstance()->getOdgRegions() as $region) {
                if(!count($this->drev->getProduits($region))) {
                    continue;
                }
                $this->regions[] = $region;
            }
        } else {
            $this->regions[] = $region;
        }
        if (!$filename) {
            $filename = $this->getFileName(true);
        }

        parent::__construct($type, $use_cache, $file_dir, $filename);
    }

    public function create() {
        foreach ($this->regions as $region) {
            $this->printable_document->addPage($this->getPartial('drev/pdf', array('drev' => $this->drev, 'region' => $region)));
        }
    }

    public function getRegion() {
        $region = null;

        if(count($this->regions) == 1 && $this->regions[0]) {
            $region = $this->regions[0];
        }

        return $region;
    }

    public function generate() {
        if(count($this->regions) <= 1) {

            return parent::generate();
        }

        if($this->printable_document instanceof PageableHTML) {
            return parent::generate();
        }

        $files = array();
        foreach ($this->regions as $region) {
            $pdf = new ExportDRevPDF($this->drev, $region);
            $pdf->setPartialFunction($this->getPartialFunction());
            $pdf->removeCache();
            $pdf->generate();

            $files[] = $pdf->getFile();
        }

        shell_exec('pdftk '.implode(' ', $files).' cat output '.$this->getFileName(true));
    }

    public function output() {
        if(count($this->regions) <= 1) {

            return parent::output();
        }

        if($this->printable_document instanceof PageableHTML) {
            return parent::output();
        }

        return file_get_contents($this->getFile());
    }

    public function getFile() {
        if(count($this->regions) <= 1) {

            return parent::getFile();
        }

        if($this->printable_document instanceof PageableHTML) {
            return parent::getFile();
        }

        return $this->getFileName(true);
    }

    protected function getHeaderTitle() {
        $titre = sprintf("Déclaration de Revendication %s", $this->drev->campagne);

        $region = $this->getRegion();
        if($region) {
            $titre .= " (".$region.")";
        }

        return $titre;
    }

    protected function getHeaderSubtitle() {

        $header_subtitle = sprintf("%s\n\n", $this->drev->declarant->nom
        );

        if (!$this->drev->isPapier() && $this->drev->validation && $this->drev->validation !== true) {
            $date = new DateTime($this->drev->validation);
            $header_subtitle .= sprintf("Signé électroniquement via l'application de télédéclaration le %s", $date->format('d/m/Y'));
        } elseif(!$this->drev->isPapier()) {
            $header_subtitle .= sprintf("Exemplaire brouillon");
        }

        if ($this->drev->isPapier() && $this->drev->validation && $this->drev->validation !== true) {
            $date = new DateTime($this->drev->validation);
            $header_subtitle .= sprintf("Reçue le %s", $date->format('d/m/Y'));
        }

        return $header_subtitle;
    }


    protected function getConfig() {
        $config = parent::getConfig();

        $region = null;

        if(count($this->regions) == 1 && $this->regions[0]) {
            $region = $this->regions[0];
        }

        if($region && file_exists(sfConfig::get('sf_web_dir').'/images/pdf/logo_'.strtolower($region).'.jpg')) {
            $config->header_logo = 'logo_'.strtolower($region).'.jpg';
        }

        return $config;
    }

    public function getFileName($with_rev = false) {

        return self::buildFileName($this->drev, true, $this->getRegion());
    }

    public static function buildFileName($drev, $with_rev = false, $region = null) {
        $filename = sprintf("DREV_%s_%s", $drev->identifiant, $drev->campagne);

        $declarant_nom = strtoupper(KeyInflector::slugify($drev->declarant->nom));
        $filename .= '_' . $declarant_nom;

        if ($with_rev) {
            $filename .= '_' . $drev->_rev;
        }

        if($region) {
            $filename .= '_'.$region;
        }

        return $filename . '.pdf';
    }

}
