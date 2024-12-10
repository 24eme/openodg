<?php

class ExportDRevPDF extends ExportPDF {

    protected $drev = null;
    protected $regions_multi_pdf = array();
    protected $region = null;
    protected $infos = [];

    public function __construct($drev, $region = null, $type = 'pdf', $use_cache = false, $file_dir = null, $filename = null) {
        $this->drev = $drev;
        $this->region = $region;
        if(! $this->region && RegionConfiguration::getInstance()->hasOdgProduits() && ! DrevConfiguration::getInstance()->hasPDFUniqueRegion()) {
            $this->regions_multi_pdf = $this->drev->declaration->getSyndicats();
        } elseif($this->region) {
            $this->infos = RegionConfiguration::getInstance()->getOdgRegionInfos($this->region);

            if (isset($this->infos['nom']) === false) {
                $infoOrga = Organisme::getInstance($this->region);
                $this->infos['nom'] = $infoOrga->getNom();
                $this->infos['adresse'] = implode(', ', [$infoOrga->getAdresse(), $infoOrga->getCodePostal(), $infoOrga->getCommune()]);
                $this->infos['telephone'] = $infoOrga->getTelephone();
                $this->infos['email'] = $infoOrga->getEmail();
            }
        }

        if (!$filename) {
            $filename = $this->getFileName(true);
        }

        parent::__construct($type, $use_cache, $file_dir, $filename);
    }

    public function create() {
        $this->printable_document->addPage($this->getPartial('drev/pdf', array('drev' => $this->drev, 'region' => $this->region)));
    }

    public function generate() {
        if(!count($this->regions_multi_pdf)) {

            return parent::generate();
        }

        if($this->printable_document instanceof PageableHTML) {
            return parent::generate();
        }

        $files = array();
        foreach ($this->regions_multi_pdf as $region) {
            $pdf = new ExportDRevPDF($this->drev, $region);
            $pdf->setPartialFunction($this->getPartialFunction());
            $pdf->removeCache();
            $pdf->generate();

            $files[] = $pdf->getFile();
        }

        shell_exec('pdftk '.implode(' ', $files).' cat output '.$this->getFile());
    }

    public function output() {
        if(!count($this->regions_multi_pdf)) {

            return parent::output();
        }

        if($this->printable_document instanceof PageableHTML) {
            return parent::output();
        }

        return file_get_contents($this->getFile());
    }

    public function getFile() {
        if(count($this->regions_multi_pdf) <= 1) {

            return parent::getFile();
        }

        if($this->printable_document instanceof PageableHTML) {
            return parent::getFile();
        }

        return sfConfig::get('sf_cache_dir').'/pdf/'.$this->getFileName(true);
    }

    protected function getHeaderTitle() {
        $titre = sprintf("Déclaration de Revendication %s", $this->drev->campagne);
        if($this->region) {
            $titre .= " (".$this->infos['nom'].")";
        }

        if ($this->drev->_rev === null) {
            $titre .= " - Numéro de dossier " . $this->drev->lots[0]->getNumeroDossier();
        }

        return $titre;
    }

    protected function getFooterText() {
        if(!$this->region) {

            return sprintf(
                "<span style='color:#ff0000;'>%s - %s - %s - %s - %s - %s</span>",
                Organisme::getInstance()->getNom(),
                Organisme::getInstance()->getAdresse(),
                Organisme::getInstance()->getCodePostal(),
                Organisme::getInstance()->getCommune(),
                Organisme::getInstance()->getTelephone(),
                Organisme::getInstance()->getEmail()
            );
        }

        return sprintf("%s - %s", $this->infos['nom'], $this->infos['adresse']);
    }

    protected function getHeaderSubtitle() {

        $header_subtitle = sprintf("%s\n\n", $this->drev->declarant->nom
        );
        if (!$this->drev->isPapier() && $this->drev->getDateDepot()) {
            $date = new DateTime($this->drev->getDateDepot());
            $header_subtitle .= sprintf("Signé électroniquement via l'application de télédéclaration le %s", $date->format('d/m/Y'));
            if($this->region && $this->drev->getValidationOdgDateByRegion($this->region)){
              $dateOdg = new DateTime($this->drev->getValidationOdgDateByRegion($this->region));
              $header_subtitle .= ", validée par l'ODG le ".$dateOdg->format('d/m/Y');
            }elseif($this->region){
                $header_subtitle .= ", en attente de l'approbation par l'ODG";
            }
            if(!$this->region && $this->drev->validation_odg) {
                $dateOdg = new DateTime($this->drev->validation_odg);
                $header_subtitle .= ", validée par l'ODG le ".$dateOdg->format('d/m/Y');
            } elseif(!$this->region) {
                $header_subtitle .= ", en attente de l'approbation par l'ODG";
            }

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
        $config = parent::getConfig();

        if($this->region && file_exists(sfConfig::get('sf_web_dir').'/images/pdf/logo_'.strtolower($this->region).'.jpg')) {
            $config->header_logo = 'logo_'.strtolower($this->region).'.jpg';
        }
        $config->header_string = $this->getHeaderSubtitle();
        return $config;
    }

    public function getFileName($with_rev = false) {

        return self::buildFileName($this->drev, true, $this->region);
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
