<?php

class ExportDegustationFicheLotsAPreleverPDF extends ExportDeclarationLotsPDF {

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
        $this->engine();
        parent::__construct($degustation,$type, $use_cache, $file_dir, $filename);
    }

    public function engine() {
      foreach ($this->degustation->getLotsPrelevables() as $lot) {
          if ($this->secteur && $this->secteur != $lot->secteur) {
              continue;
          }
          if(!isset($this->etablissements[$lot->declarant_identifiant])) {
              $this->etablissements[$lot->declarant_identifiant] = EtablissementClient::getInstance()->findByIdentifiant($lot->declarant_identifiant);
          }
          $this->lots[$lot->getLogementCodePostal().'/'.$lot->declarant_identifiant][$lot->getNumeroDossier()][] = $lot;
      }
      ksort($this->lots);
    }

    public function create() {
      @$this->printable_document->addPage(
        $this->getPartial('degustation/ficheLotsAPrelevesPdf',
          array(
            'degustation' => $this->degustation,
            'etablissements' => $this->etablissements,
            'lots' => $this->lots
          )
        ));
    }

    protected function getHeaderTitle() {
        $title = "Fiche de tournée";
        if ($this->secteur) {
            $title .= " – $this->secteur";
        }
        return $title;
    }

    protected function getHeaderSubtitle() {
        $nbOperateurs = count($this->etablissements);
        $nbLots = count($this->lots);
        $prelevement = ($nbOperateurs > 1)? "$nbOperateurs opérateurs" : "$nbOperateurs opérateur";
        $prelevement .= ($nbLots > 1)? " pour $nbLots lots à prélever" : " pour $nbLots lot à prélever";
        $header_subtitle = sprintf("Dégustation du %s, %s", $this->degustation->getDateFormat('d/m/Y'), $this->degustation->lieu);
        $header_subtitle .= sprintf("\n$prelevement");
        return $header_subtitle;
    }

    public function getFileName($with_rev = false) {
        $filename = sprintf("fiche_tournee_prelevements_%s", $this->degustation->_id);
        if ($with_rev) {
            $filename .= '_' . $this->degustation->_rev;
        }
        return $filename . '.pdf';
    }

    protected function getConfig() {
        return new ExportDegustationFicheLotsAPreleverPDFConfig();
    }

}
