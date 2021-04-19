<?php

class ExportDegustationFicheIndividuelleLotsAPreleverPDF extends ExportPDF {

    protected $degustation = null;
    protected $courrierInfos = null;

    public function __construct($degustation, $type = 'pdf', $use_cache = false, $file_dir = null, $filename = null) {
        $this->degustation = $degustation;

        if (!$filename) {
            $filename = $this->getFileName(true);
        }

        $app = strtoupper(sfConfig::get('sf_app'));
        $courrierInfos = sfConfig::get('app_facture_emetteur');
        $this->courrierInfos = $courrierInfos[$app];

        parent::__construct($type, $use_cache, $file_dir, $filename);
    }

    public function create() {

      $adresses = array();
      foreach ($this->degustation->getLots() as $lot) {
          if ($lot->isLeurre()){
              continue;
          }
          $adresses[$lot->adresse_logement][$lot->unique_id] = $lot;
      }
      ksort($adresses);
      foreach ($adresses as $adresseLogement => $lotsArchive) {
        $volumeLotTotal = 0;
        foreach ($lotsArchive as $archive => $lot) {
          $volumeLotTotal += $lot->volume;
        }

        $etablissement = EtablissementClient::getInstance()->findByIdentifiant($lotsArchive[array_key_first($lotsArchive)]->declarant_identifiant);
        $adresseLogement = $lot->adresse_logement;
        if(boolval($adresseLogement) === false){
            $adresseLogement = sprintf("%s — %s — %s — %s",$etablissement->nom, $etablissement->getAdresse(), $etablissement->code_postal, $etablissement->commune);
        }
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
        $titre = $this->courrierInfos["service_facturation"];
        return $titre;
    }

    protected function getHeaderSubtitle() {
        $header_subtitle = sprintf("Fiche de prélevement\n\nDate de commission : %s\nLieu de dégustation : %s\n",
                                    $this->degustation->getDateFormat('d/m/Y'),
                                    $this->degustation->lieu
                                  );
        return $header_subtitle;
    }


    protected function getFooterText() {
        return sprintf("\n%s     %s - %s - %s   %s    %s\n", $this->courrierInfos['service_facturation'], $this->courrierInfos['adresse'], $this->courrierInfos['code_postal'], $this->courrierInfos['ville'], $this->courrierInfos['telephone'], $this->courrierInfos['email']);
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
