<?php
class ExportConditionnementPDF extends ExportPDF {

    protected $declaration = null;
    protected $etablissement = null;

    public function __construct($declaration, $type = 'pdf', $use_cache = false, $file_dir = null, $filename = null) {
        $this->declaration = $declaration;
        $this->etablissement = $declaration->getEtablissementObject();
        $app = strtoupper(sfConfig::get('sf_app'));

        if (!$filename) {
            $filename = $this->getFileName(true);
        }
        parent::__construct($type, $use_cache, $file_dir, $filename);
    }

    public function create() {
        @$this->printable_document->addPage($this->getPartial('conditionnement/pdf', array('document' => $this->declaration, 'etablissement' => $this->etablissement)));
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

    public function getFileName($with_rev = false) {

        return self::buildFileName($this->declaration, true);
    }

    public static function buildFileName($declaration, $with_rev = false) {
        $filename = sprintf("_%s", $declaration->_id);

        if ($with_rev) {
            $filename .= '_' . $declaration->_rev;
        }

        return $filename . '.pdf';
    }

    protected function getHeaderTitle() {
        $date = new DateTime($this->declaration->date);
        $titre = sprintf("Déclaration de Conditionnement du %s", $date->format('d/m/Y'));
        return $titre;
    }

    protected function getFooterText() {
        return sprintf("<br/>%s     %s - %s - %s<br/>%s    %s", Organisme::getInstance()->getNom(), Organisme::getInstance()->getAdresse(), Organisme::getInstance()->getCodePostal(), Organisme::getInstance()->getCommune(), Organisme::getInstance()->getTelephone(), Organisme::getInstance()->getEmail());
    }

    protected function getHeaderSubtitle() {
        $header_subtitle = sprintf("%s\n\n", $this->declaration->declarant->nom);
        if (!$this->declaration->isPapier() && $this->declaration->validation && $this->declaration->validation !== true) {
            $date = new DateTime($this->declaration->validation);
            $header_subtitle .= sprintf("Signé électroniquement via l'application de télédéclaration le %s", $date->format('d/m/Y'));
            if($this->declaration->validation_odg) {
                $dateOdg = new DateTime($this->declaration->validation_odg);
                $header_subtitle .= ", validée par l'ODG le ".$dateOdg->format('d/m/Y');
            } else {
                $header_subtitle .= ", en attente de l'approbation par l'ODG";
            }

        } elseif(!$this->declaration->isPapier()) {
            $header_subtitle .= sprintf("Exemplaire brouillon");
        }

        if ($this->declaration->isPapier() && $this->declaration->validation && $this->declaration->validation !== true) {
            $date = new DateTime($this->declaration->validation);
            $header_subtitle .= sprintf("Reçue le %s", $date->format('d/m/Y'));
        }
        return $header_subtitle;
    }

    protected function getConfig() {
        return new ExportConditionnementPDFConfig();
    }
}
