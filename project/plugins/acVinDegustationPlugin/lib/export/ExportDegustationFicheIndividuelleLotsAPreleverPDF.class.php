<?php

class ExportDegustationFicheIndividuelleLotsAPreleverPDF extends ExportPDF {

    protected $degustation = null;

    public function __construct($degustation, $type = 'pdf', $use_cache = false, $file_dir = null, $filename = null) {
        $this->degustation = $degustation;

        if (!$filename) {
            $filename = $this->getFileName(true);
        }
        parent::__construct($type, $use_cache, $file_dir, $filename);
    }

    public function create() {

      $adresses = array();
      foreach ($this->degustation->getLots() as $lot) {
          $adresses[$lot->adresse_logement][$lot->getNumeroArchive()] = $lot;
      }

      foreach ($adresses as $adresseLogement => $lotsArchive) {
        $volumeLotTotal = 0;
        foreach ($lotsArchive as $archive => $lot) {
          $volumeLotTotal += $lot->volume;
        }

        $etablissement = EtablissementClient::getInstance()->findByIdentifiant($lotsArchive[array_key_first($lotsArchive)]->declarant_identifiant);
        @$this->printable_document->addPage(
          $this->getPartial('degustation/ficheIndividuelleLotsAPreleverPdf',
          array(
            'degustation' => $this->degustation,
            'etablissement' => $etablissement,
            'volumeLotTotal' => $volumeLotTotal,
            'lots' => $lotsArchive,
            'adresseLogement' => $adresseLogement
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
        $date = substr($this->degustation->date,0,10);
        $date = $date[8].$date[9].'/'.$date[5].$date[6].'/'.$date[0].$date[1].$date[2].$date[3];
        $header_subtitle = sprintf("%s\n\n", $this->degustation->lieu) . "Fiche de prélèvement (Liste des lots à prélever)  Date de commission prévu : ".$date;
        return $header_subtitle;
    }


    protected function getFooterText() {
        $footer= sprintf($this->degustation->getNomOrganisme()." — %s", $this->degustation->getLieuNom());
        return $footer;
    }

    protected function getConfig() {

        return new ExportDegustationFicheIndividuelleLotsAPreleverPDFConfig();
    }

    public function getFileName($with_rev = false) {

        return self::buildFileName($this->degustation, true);
    }

    public static function buildFileName($degustation, $with_rev = false) {
        $filename = sprintf("fiche_individuelle_prelevements_%s", $degustation->_id);


        if ($with_rev) {
            $filename .= '_' . $degustation->_rev;
        }


        return $filename . '.pdf';
    }

}
