<?php

class ExportDegustationFicheIndividuelleLotsAPreleverPDF extends ExportPDF {

    protected $degustation = null;
    protected $secteur = null;
    protected $etablissements = [];
    protected $lots = [];

    public function __construct($degustation, $type = 'pdf', $use_cache = false, $file_dir = null, $filename = null, $secteur = null) {
        $this->degustation = $degustation;
        if($secteur == DegustationClient::DEGUSTATION_SANS_SECTEUR) {
            $secteur = null;
        }
        $this->secteur = $secteur;
        if (!$filename) {
            $filename = $this->getFileName(true);
        }
        $this->engine();
        parent::__construct($type, $use_cache, $file_dir, $filename);
    }

    public function engine() {
      foreach ($this->degustation->getLotsPrelevables() as $lot) {
          if ($this->secteur && $this->secteur != $lot->secteur) {
              continue;
          }
          if(!isset($this->etablissements[$lot->declarant_identifiant])) {
              $this->etablissements[$lot->adresse_logement.$lot->declarant_identifiant] = EtablissementClient::getInstance()->findByIdentifiant($lot->declarant_identifiant);
          }
          $this->lots[$lot->adresse_logement.$lot->declarant_identifiant][$lot->unique_id] = $lot;
      }
      ksort($this->lots);
    }

    public function create() {
        if (DegustationConfiguration::getInstance()->isAnonymisationManuelle()) {
            @$this->printable_document->addPage(
                $this->getPartial('degustation/ficheIndividuelleLotsSynthetiqueAPreleverPdf',
                    array(
                      'degustation' => $this->degustation,
                      'etablissements' => $this->etablissements,
                      'lots' => $this->lots
                    )
            ));
        } else {
            foreach ($this->lots as $lotsArchive) {
              $volumeLotTotal = 0;
              foreach ($lotsArchive as $archive => $lot) {
                $volumeLotTotal += $lot->volume;
              }
              $etablissement = $this->etablissements[$lot->adresse_logement.$lot->declarant_identifiant];
              $adresseLogement = $lot->adresse_logement;
              if(boolval($adresseLogement) === false){
                  $adresseLogement = sprintf("%s — %s — %s %s",$etablissement->nom, $etablissement->getAdresse(), $etablissement->code_postal, $etablissement->commune);
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
        $title = "Fiche de prélevement";
        if ($this->secteur) {
            $title .= " – $this->secteur";
        }
        return $title;
    }

    protected function getHeaderSubtitle() {
        $nbOperateurs = count($this->etablissements);
        $nbLots = count($this->lots);
        $header_subtitle = sprintf("Dégustation du %s, %s", $this->degustation->getDateFormat('d/m/Y'), $this->degustation->lieu);
        $header_subtitle .= sprintf("\n");
        return $header_subtitle;
    }

    protected function getConfig() {
        return new ExportDegustationFicheIndividuelleLotsAPreleverPDFConfig();
    }

    public function getFileName($with_rev = false) {
        $filename = sprintf("fiche_individuelle_prelevements_%s", $this->degustation->_id);
        if ($with_rev) {
            $filename .= '_' . $this->degustation->_rev;
        }
        return $filename . '.pdf';
    }

    protected function getFooterText() {
        return sprintf("<br/>%s     %s - %s - %s<br/>%s    %s", Organisme::getInstance(null, 'degustation')->getNom(), Organisme::getInstance(null, 'degustation')->getAdresse(), Organisme::getInstance(null, 'degustation')->getCodePostal(), Organisme::getInstance(null, 'degustation')->getCommune(), Organisme::getInstance(null, 'degustation')->getTelephone(), Organisme::getInstance(null, 'degustation')->getEmail());
    }

}
