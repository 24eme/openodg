<?php

class ExportDegustationFicheIndividuelleLotsAPreleverPDF extends ExportDegustationPDF {

    protected $secteur = null;
    protected $etablissements = [];
    protected $lots = [];
    protected $lotid = null;

    public function __construct($degustation, $lotid = null, $type = 'pdf', $use_cache = false, $file_dir = null, $filename = null, $secteur = null) {
        parent::__construct($degustation, $type, $use_cache, $file_dir, $filename);
        $this->lotid = $lotid;
        if($secteur == DegustationClient::DEGUSTATION_SANS_SECTEUR) {
            $secteur = null;
        }
        $this->secteur = $secteur;
        $this->engine();
    }

    public function engine() {
      foreach ($this->degustation->getLotsPrelevables() as $lot) {
          if ($this->secteur && $this->secteur != $lot->secteur) {
              continue;
          }
          if ($this->lotid && $this->lotid != $lot->unique_id) {
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
        sfApplicationConfiguration::getActive()->loadHelpers(array('Partial'));
        try {
            return get_partial('degustation/ficheIndividuelleLotsAPreleverPdfHeader', ['degustation' => $this->degustation]);
        } catch (Exception $e) {
            $title = "Fiche de prélevement";
            if ($this->secteur) {
                $title .= " – $this->secteur";
            }
            return $title;
        }
    }

    protected function getHeaderSubtitle()
    {
        sfApplicationConfiguration::getActive()->loadHelpers(array('Partial'));
        try {
            return get_partial('degustation/ficheIndividuelleLotsAPreleverPdfHeaderSubtitle', ['degustation' => $this->degustation]);
        } catch (Exception $e) {
            if($this->degustation->type == TourneeClient::TYPE_MODEL) {
                return sprintf("\nTournée du %s\n\n", $this->degustation->getDateFormat('d/m/Y'));
            } else {
                $header_subtitle = sprintf("\nDégustation du %s", $this->degustation->getDateFormat('d/m/Y'));
                $header_subtitle .= sprintf("\n%s", $this->degustation->lieu);
                return $header_subtitle;
            }
        }
    }

    public function getFileName($with_rev = false) {
        $filename = sprintf("fiche_individuelle_prelevements_%s", $this->degustation->_id);
        if ($with_rev) {
            $filename .= '_' . $this->degustation->_rev;
        }
        return $filename . '.pdf';
    }

    protected function getFooterText() {
        return sprintf("<br/>%s     %s - %s - %s<br/>%s    %s", Organisme::getInstance($this->degustation->region, 'degustation')->getNom(), Organisme::getInstance($this->degustation->region, 'degustation')->getAdresse(), Organisme::getInstance($this->degustation->region, 'degustation')->getCodePostal(), Organisme::getInstance($this->degustation->region, 'degustation')->getCommune(), Organisme::getInstance($this->degustation->region, 'degustation')->getTelephone(), Organisme::getInstance($this->degustation->region, 'degustation')->getEmail());
    }

}
