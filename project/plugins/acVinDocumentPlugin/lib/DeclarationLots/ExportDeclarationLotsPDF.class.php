<?php
class ExportDeclarationLotsPDF extends ExportPDF {

    protected $declarationLot = null;
    protected $etablissement = null;
    protected $adresse;

    public function __construct($declarationLot, $type = 'pdf', $use_cache = false, $file_dir = null, $filename = null) {
        $this->declarationLot = $declarationLot;
        $this->etablissement = $declarationLot->getEtablissementObject();
        $this->adresse = sfConfig::get('app_degustation_courrier_adresse');
        if (!$filename) {
            $filename = $this->getFileName(true);
        }
        parent::__construct($type, $use_cache, $file_dir, $filename);
        if($this->printable_document->getPdf()){
          $this->printable_document->getPdf()->setPrintHeader(true);
          $this->printable_document->getPdf()->setPrintFooter(true);
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
        $title = '';
        return $title;
    }

    protected function getFooterText() {
        return sprintf("%s     %s - %s  %s    %s\n\n", $this->adresse['raison_sociale'], $this->adresse['adresse'], $this->adresse['cp_ville'], $this->adresse['telephone'], $this->adresse['email']);
    }

    protected function getHeaderSubtitle() {

        $header_subtitle = sprintf("%s\n\n", $this->declarationLot->declarant->nom);
        if (!$this->declarationLot->isPapier() && $this->declarationLot->validation && $this->declarationLot->validation !== true) {
            $date = new DateTime($this->declarationLot->validation);
            $header_subtitle .= sprintf("Signé électroniquement via l'application de télédéclaration le %s", $date->format('d/m/Y'));
            if($this->declarationLot->validation_odg) {
                $dateOdg = new DateTime($this->declarationLot->validation_odg);
                $header_subtitle .= ", validée par l'ODG le ".$dateOdg->format('d/m/Y');
            } else {
                $header_subtitle .= ", en attente de l'approbation par l'ODG";
            }

        } elseif(!$this->declarationLot->isPapier()) {
            $header_subtitle .= sprintf("Exemplaire brouillon");
        }

        if ($this->declarationLot->isPapier() && $this->declarationLot->validation && $this->declarationLot->validation !== true) {
            $date = new DateTime($this->declarationLot->validation);
            $header_subtitle .= sprintf("Reçue le %s", $date->format('d/m/Y'));
        }
        return $header_subtitle;
    }

    public function getFileName($with_rev = false) {
        return self::buildFileName($this->declarationLot, true);
    }

    public static function buildFileName($declarationLot, $with_rev = false) {
        $filename = $declarationLot->_id;
        if ($with_rev) {
            $filename .= '_' . $declarationLot->_rev;
        }
        return $filename . '.pdf';
    }

    protected function create() { throw new Exception('create method not implemented'); }
}
