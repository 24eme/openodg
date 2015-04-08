<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ExportDegustationPDF
 *
 * @author mathurin
 */
class ExportDegustationPDF extends ExportPDF {

    protected $prelevement = null;
    protected $degustation = null;
    protected $operateur = null;

    public function __construct($degustation, $prelevement, $type = 'pdf', $use_cache = false, $file_dir = null, $filename = null) {
        $this->degustation = $degustation;
        $this->prelevement = $prelevement;
        if (!$filename) {
            $filename = $this->getFileName(true);
        }
        
        parent::__construct($type, $use_cache, $file_dir, $filename);
    }

    public function create() {
        $this->printable_document->addPage($this->getPartial('degustation/pdf', array(
            'degustation' => $this->degustation,
            'prelevement' => $this->prelevement)));
    }

    protected function getHeaderTitle() {
        return "Association des viticulteurs d'Alsace";
    }

    protected function getHeaderSubtitle() {
        return "Le syndicat général de défense des Appellations";
    }

    public function getFileName($with_rev = false) {

        return self::buildFileName($this->degustation, $this->prelevement, true);
    }

    public static function buildFileName($degustation, $prelevement, $with_rev = false) {
        $filename = 'DEGUSTATION_' . strtoupper(KeyInflector::slugify($degustation->raison_sociale)). '_'.strtoupper(KeyInflector::slugify($prelevement->libelle));

        if ($with_rev) {
            $filename .= '_' . $degustation->_rev;
        }

        return $filename . '.pdf';
    }

}
