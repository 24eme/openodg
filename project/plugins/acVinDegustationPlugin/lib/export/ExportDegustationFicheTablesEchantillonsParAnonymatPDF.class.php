<?php

class ExportDegustationFicheTablesEchantillonsParAnonymatPDF extends ExportPDF {

    protected $degustation = null;

    public function __construct($degustation, $type = 'pdf', $use_cache = false, $file_dir = null, $filename = null) {
        $this->degustation = $degustation;

        if (!$filename) {
            $filename = $this->getFileName(true);
        }
        parent::__construct($type, $use_cache, $file_dir, $filename);
    }

    public function create()
    {
        $lotsTries = [];
        $lots = $this->degustation->getLotsDegustables();

        foreach ($lots as $lot) {
            $lotsTries[$lot->numero_table][] = $lot;
        }

        foreach ($lotsTries as $table => &$lots_table) {
            usort($lots_table, function ($a, $b) {
                return strcmp($a->numero_anonymat, $b->numero_anonymat);
            });
        }

        ksort($lotsTries);

        @$this->printable_document->addPage(
          $this->getPartial('degustation/ficheTablesEchantillonsParAnonymatPdf',
          array(
            'degustation' => $this->degustation,
            'lots' => $lotsTries
          )
        ));
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
        return sprintf("%s\n\n", $this->degustation->lieu)." Liste des lots ventilés anonymisés par table";
    }


    protected function getFooterText() {
        $footer= sprintf($this->degustation->getNomOrganisme()." — %s", $this->degustation->getLieuNom());
        return $footer;
    }

    protected function getConfig() {

        return new ExportDegustationFicheTablesEchantillonsParAnonymatPDFConf();
    }

    public function getFileName($with_rev = false) {

        return self::buildFileName($this->degustation, true);
    }

    public static function buildFileName($degustation, $with_rev = false) {
        $filename = sprintf("fiche_echantillons_table_par_anonymat_%s", $degustation->_id);


        if ($with_rev) {
            $filename .= '_' . $degustation->_rev;
        }


        return $filename . '.pdf';
    }

}
