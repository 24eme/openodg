<?php

class ExportDegustationFicheIndividuelleLotsAPreleverPDF extends ExportPDF {

    protected $degustation = null;
    protected $lotid = null;

    public function __construct($degustation, $lotid = null, $type = 'pdf', $use_cache = false, $file_dir = null, $filename = null) {
        $this->degustation = $degustation;
        $this->lotid = $lotid;

        if (!$filename) {
            $filename = $this->getFileName(true);
        }

        parent::__construct($type, $use_cache, $file_dir, $filename);
    }

    public function create() {
      $adresseFilter = null;
      $adresses = array();
      $lots = $etablissements = array();
      foreach ($this->degustation->getLotsPrelevables() as $lot) {
          $adresses[$lot->adresse_logement.$lot->declarant_identifiant][$lot->unique_id] = $lot;
          if ($this->lotid == $lot->unique_id) {
              $adresseFilter = $lot->adresse_logement.$lot->declarant_identifiant;
          }
      }
      if ($adresseFilter) {
          $adresses = [$adresseFilter => $adresses[$adresseFilter]];
      } else {
          ksort($adresses);
      }

      foreach ($adresses as $lotsArchive) {
        $volumeLotTotal = 0;
        foreach ($lotsArchive as $archive => $lot) {
          $volumeLotTotal += $lot->volume;
        }

        $etablissement = EtablissementClient::getInstance()->findByIdentifiant($lotsArchive[array_key_first($lotsArchive)]->declarant_identifiant);
        $adresseLogement = $lot->adresse_logement;
        if(boolval($adresseLogement) === false){
            $adresseLogement = sprintf("%s — %s — %s — %s",$etablissement->nom, $etablissement->getAdresse(), $etablissement->code_postal, $etablissement->commune);
        }
        if (! DegustationConfiguration::getInstance()->isAnonymisationManuelle() ) {
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
        } else {
            $lots[$adresseLogement] = $lotsArchive;
            $etablissements[$adresseLogement] = $etablissement;
        }
      }

        if (DegustationConfiguration::getInstance()->isAnonymisationManuelle() ) {
            @$this->printable_document->addPage(
            $this->getPartial('degustation/ficheIndividuelleLotsSynthetiqueAPreleverPdf',
              array(
                'degustation' => $this->degustation,
                'etablissements' => $etablissements,
                "date_edition" => date("d/m/Y"),
                "nbLotTotal" => count($this->degustation->getLotsPrelevables()),
                'lots' => $lots
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
        $titre = Organisme::getInstance(null, 'degustation')->getNom();
        return $titre;
    }

    protected function getHeaderSubtitle() {
        if (DegustationConfiguration::getInstance()->isAnonymisationManuelle() ) {
            $header_subtitle = "Fiche de prélevement";
        } else {
            $header_subtitle = sprintf("Fiche de prélevement\n\nDate de commission : %s\nLieu de dégustation : %s\n",
                                        $this->degustation->getDateFormat('d/m/Y'),
                                        $this->degustation->lieu
                                      );
        }
        return $header_subtitle;
    }


    protected function getFooterText() {
        return sprintf("\n%s     %s - %s - %s   %s    %s\n", Organisme::getInstance(null, 'degustation')->getNom(), Organisme::getInstance(null, 'degustation')->getAdresse(), Organisme::getInstance(null, 'degustation')->getCodePostal(), Organisme::getInstance(null, 'degustation')->getCommune(), Organisme::getInstance(null, 'degustation')->getTelephone(), Organisme::getInstance(null, 'degustation')->getEmail());
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
