<?php

class ExportChgtDenomPDF extends ExportPDF {

    protected $chgtdenom = null;
    protected $etablissement = null;
    protected $changement = null;
    protected $total = false;
    protected $courrierInfos;

    public function __construct($chgtdenom, $type = 'pdf', $use_cache = false, $file_dir = null, $filename = null) {
        $this->chgtdenom = $chgtdenom;
        $this->etablissement = $chgtdenom->getEtablissementObject();

        $this->changement = $chgtdenom->getChangementType();
        $this->total = $chgtdenom->isTotal();

        $app = strtoupper(sfConfig::get('sf_app'));
        $this->courrierInfos = sfConfig::get('app_facture_emetteur')[$app];

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
        $this->printable_document->addPage($this->getPartial('chgtdenom/PDF', array('chgtdenom' => $this->chgtdenom, 'etablissement' => $this->etablissement, 'courrierInfos' => $this->courrierInfos, 'changement' => $this->changement, 'total' => (bool) $this->total)));
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
        return sprintf("\n\n%s     %s - %s - %s   %s    %s\n", $this->courrierInfos['service_facturation'], $this->courrierInfos['adresse'], $this->courrierInfos['code_postal'], $this->courrierInfos['ville'], $this->courrierInfos['telephone'], $this->courrierInfos['email']);
    }

    protected function getHeaderSubtitle() {

        return "";
    }


    protected function getConfig() {

        return new ExportLotPDFConfig();
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
