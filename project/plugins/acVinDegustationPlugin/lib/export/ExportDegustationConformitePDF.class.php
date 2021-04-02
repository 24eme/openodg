<?php

class ExportDegustationConformitePDF extends ExportPDF {

    protected $degustation = null;
    protected $etablissement = null;
    protected $coordonnees;
    protected $courrierInfos;

    public function __construct($degustation,$etablissement, $type = 'pdf', $use_cache = false, $file_dir = null, $filename = null) {
        $this->degustation = $degustation;
        $this->etablissement = $etablissement;
        $this->coordonnees = $this->degustation->getCoordonnees();

        $app = strtoupper(sfConfig::get('sf_app'));
        $courrierInfos = sfConfig::get('app_facture_emetteur');
        $this->courrierInfos = $courrierInfos[$app];


        if (!$filename) {
            $filename = $this->getFileName(true);
        }
        parent::__construct($type, $use_cache, $file_dir, $filename);
        if($this->printable_document->getPdf()){
          $this->printable_document->getPdf()->setPrintHeader(true);
          $this->printable_document->getPdf()->setPrintFooter(true);
        }
    }

    public function create() {
        $lots = array();
        foreach ($this->degustation->getLots() as $lot) {
            if ($lot->declarant_identifiant == $this->etablissement->identifiant && ($lot->conformite == Lot::CONFORMITE_CONFORME || !$lot->conformite) && ($lot->statut == Lot::STATUT_PRELEVE || $lot->statut == Lot::STATUT_CONFORME) ) {
                $lots[] = $lot;
            }
        }
        $footer= sprintf($this->degustation->getNomOrganisme()." — %s", $this->degustation->getLieuNom());
        $this->printable_document->addPage($this->getPartial('degustation/degustationConformitePDF', array("footer" => $footer, 'degustation' => $this->degustation, 'etablissement' => $this->etablissement, 'lots' => $lots, 'courrierInfos' => $this->courrierInfos)));
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
        $title = $this->degustation->getNomOrganisme();
        return $title;
    }

    protected function getFooterText() {
        return $this->degustation->getNomOrganisme()." — ".$this->coordonnees['adresse']
        ." ". $this->coordonnees['ville'] ." ". $this->coordonnees['code_postal']
         ." ". $this->coordonnees['telephone'] ." ". $this->coordonnees['email'];
    }

    protected function getHeaderSubtitle() {

        return "";
    }


    protected function getConfig() {

        return new ExportDeclarationLotsPDFConfig();
    }

    public function getFileName($with_rev = false) {

        return self::buildFileName($this->degustation, true);
    }

    public static function buildFileName($degustation, $with_rev = false) {
        $filename = sprintf("CONFORMITE_%s", $degustation->_id);
        if ($with_rev) {
            $filename .= '_' . $degustation->_rev;
        }

        return $filename . '.pdf';
    }

}
