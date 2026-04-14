<?php

class ExportDegustationFicheTablesEchantillonsParAnonymatPDF extends ExportDegustationPDF {

    public function __construct($degustation, $type = 'pdf', $use_cache = false, $file_dir = null, $filename = null) {
        parent::__construct($degustation, $type, $use_cache, $file_dir, $filename);
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
        sfApplicationConfiguration::getActive()->loadHelpers(array('Partial'));
        try {
            return get_partial('degustation/ficheTablesEchantillonsParAnonymatPdfHeader', ['degustation' => $this->degustation]);
        } catch (Exception $e) {
            return "Liste des lots ventilés anonymisés par table";
        }
    }

    protected function getHeaderSubtitle()
    {
        sfApplicationConfiguration::getActive()->loadHelpers(array('Partial'));
        try {
            return get_partial('degustation/ficheTablesEchantillonsParAnonymatPdfHeaderSubtitle', ['degustation' => $this->degustation]);
        } catch (Exception $e) {
            $header_subtitle = sprintf("\nDégustation du %s", $this->degustation->getDateFormat('d/m/Y'));
            $header_subtitle .= sprintf("\n%s", $this->degustation->lieu);
            return $header_subtitle;
        }
    }

    protected function getFooterText() {
        return sprintf("<br/>%s     %s - %s - %s<br/>%s    %s", Organisme::getInstance(null, 'degustation')->getNom(), Organisme::getInstance(null, 'degustation')->getAdresse(), Organisme::getInstance(null, 'degustation')->getCodePostal(), Organisme::getInstance(null, 'degustation')->getCommune(), Organisme::getInstance(null, 'degustation')->getTelephone(), Organisme::getInstance(null, 'degustation')->getEmail());
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
