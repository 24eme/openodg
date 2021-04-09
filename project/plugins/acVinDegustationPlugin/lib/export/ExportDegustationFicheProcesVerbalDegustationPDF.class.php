<?php

class ExportDegustationFicheProcesVerbalDegustationPDF extends ExportPDF {

    protected $degustation = null;

    public function __construct($degustation, $type = 'pdf', $use_cache = false, $file_dir = null, $filename = null) {
        $this->degustation = $degustation;

        if (!$filename) {
            $filename = $this->getFileName(true);
        }
        parent::__construct($type, $use_cache, $file_dir, $filename);
    }

    public function create() {
      $etablissements = array();

      foreach ($this->degustation->getLotsDegustes() as $lot) {
          if (array_key_exists($lot->declarant_identifiant, $etablissements) === false) {
              $etablissements[$lot->declarant_identifiant] = EtablissementClient::getInstance()->findByIdentifiant($lot->declarant_identifiant);
          }
      }

      foreach ($this->degustation->getLotsDegustesByAppelation() as $appellation => $lotsDegustes) {
        @$this->printable_document->addPage(
          $this->getPartial('degustation/ficheProcesVerbalDegustationPdf',
          array(
            'degustation' => $this->degustation,
            'etablissements' => $etablissements,
            "appellation" => $appellation,
            "nbTables" => $this->degustation->getLastNumeroTable(),
            "nbDegustateurs" => count($this->degustation->getDegustateursConfirmes()),
            "nbDegustateursPresents" => count($this->degustation->getDegustateursATable()),
            'lotsDegustes' => $lotsDegustes
          )
        ));
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

        $header_subtitle = sprintf("%s - %s %s\n\nCommission: %s - Campagne: %s",
            $this->degustation->lieu,
            strstr($this->degustation->date, ' ', true),
            trim(strstr($this->degustation->date, ' ')),
            $this->degustation->_id,
            $this->degustation->campagne
        );

        return $header_subtitle;
    }


    protected function getFooterText() {
        $footer= sprintf($this->degustation->getNomOrganisme()." — %s", $this->degustation->getLieuNom());
        return $footer;
    }

    protected function getConfig() {

        return new ExportDegustationFicheProcesVerbalDegustationPDFConfig();
    }

    public function getFileName($with_rev = false) {

        return self::buildFileName($this->degustation, true);
    }

    public static function buildFileName($degustation, $with_rev = false) {
        $filename = sprintf("proces_verbal_global_%s", $degustation->_id);


        if ($with_rev) {
            $filename .= '_' . $degustation->_rev;
        }


        return $filename . '.pdf';
    }

}
