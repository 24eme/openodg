<?php

class ExportDegustationFicheLotsAPreleverPDF extends ExportPDF {

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
      foreach ($this->degustation->getLotsByNumDossierNumArchive() as $numDossier => $lotsEtablissement) {
        if (!$numDossier) {
            continue;
        }
	    $etablissement = EtablissementClient::getInstance()->findByIdentifiant($lotsEtablissement[array_key_first($lotsEtablissement)]->declarant_identifiant);
        $etablissements[$numDossier] = $etablissement;
      }
        $lots = $this->degustation->getLotsByNumDossierNumArchive();
        @$this->printable_document->addPage(
          $this->getPartial('degustation/ficheLotsAPrelevesPdf',
          array(
            'degustation' => $this->degustation,
            'etablissements' => $etablissements,
            "date_edition" => date("d/m/Y"),
            "nbLotTotal" => count($this->degustation->getLots()),
            'lots' => $lots
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
        $date = substr($this->degustation->date,0,10);
        $date = $date[8].$date[9].'/'.$date[5].$date[6].'/'.$date[0].$date[1].$date[2].$date[3];
        $header_subtitle = sprintf("%s\n\n", $this->degustation->lieu)." \nFiche de tournée (Liste des lots à prélever) Date de commission : ".$date;
        return $header_subtitle;
    }


    protected function getFooterText() {
        $footer= sprintf($this->degustation->getNomOrganisme()." — %s", $this->degustation->getLieuNom());
        return $footer;
    }

    protected function getConfig() {

        return new ExportDegustationFicheLotsAPreleverPDFConfig();
    }

    public function getFileName($with_rev = false) {

        return self::buildFileName($this->degustation, true);
    }

    public static function buildFileName($degustation, $with_rev = false) {
        $filename = sprintf("fiche_tournee_prelevements_%s", $degustation->_id);


        if ($with_rev) {
            $filename .= '_' . $degustation->_rev;
        }


        return $filename . '.pdf';
    }

}
