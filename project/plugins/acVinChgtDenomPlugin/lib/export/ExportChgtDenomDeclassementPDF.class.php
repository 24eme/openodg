<?php

class ExportChgtDenomDeclassementPDF extends ExportPDF {

    protected $chgtdenom = null;
    protected $etablissement = null;
    protected $adresse;
    protected $responsable;

    public function __construct($chgtdenom, $etablissement, $type = 'pdf', $use_cache = false, $file_dir = null, $filename = null) {
        $this->chgtdenom = $chgtdenom;
        $this->etablissement = $etablissement;
        $this->adresse = sfConfig::get('app_degustation_courrier_adresse');
        $this->responsable = sfConfig::get('app_degustation_courrier_responsable');

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
        $this->printable_document->addPage($this->getPartial('chgtdenom/declassementPDF', array('chgtdenom' => $this->chgtdenom, 'etablissement' => $this->etablissement, 'responsable' => $this->responsable)));
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
        $title = '';
        return $title;
    }

    protected function getFooterText() {
        return sprintf("%s     %s - %s  %s    %s\n\n", $this->adresse['raison_sociale'], $this->adresse['adresse'], $this->adresse['cp_ville'], $this->adresse['telephone'], $this->adresse['email']);
    }

    protected function getHeaderSubtitle() {

        return "";
    }


    protected function getConfig() {

        return new ExportChgtDenomPDFConfig();
    }

    public function getFileName($with_rev = false) {

        return self::buildFileName($this->chgtdenom, true);
    }

    public static function buildFileName($chgtdenom, $with_rev = false) {
        $filename = sprintf("DECLASSEMENT_%s", $chgtdenom->_id);
        if ($with_rev) {
            $filename .= '_' . $chgtdenom->_rev;
        }

        return $filename . '.pdf';
    }

}
