<?php

class ExportPMCPDF extends ExportPDF
{
    protected $declaration = null;
    protected $etablissement = null;
    protected $nc = null;

    public function __construct($declaration, $type = 'pdf', $use_cache = false, $file_dir = null, $filename = null) {
        $this->declaration = $declaration;
        $this->etablissement = $declaration->getEtablissementObject();
        $this->nc = $declaration->isNonConformite();
        $app = strtoupper(sfConfig::get('sf_app'));

        if (!$filename) {
            $filename = $this->getFileName(true);
        }
        parent::__construct($type, $use_cache, $file_dir, $filename);
    }

    public function create() {
        @$this->printable_document->addPage($this->getPartial('pmc/pdf', array('document' => $this->declaration, 'etablissement' => $this->etablissement)));
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
        $suffix = $this->nc ? PMCNCClient::SUFFIX : '';
        $titre = sprintf("Déclaration de Mise en Circulation%s du %s", $suffix, $this->declaration->getDateFr());
        return $titre;
    }

    protected function getFooterText() {
        return sprintf("<br/>%s    - %s - %s %s<br/>%s    %s", Organisme::getInstance(null, 'degustation')->getNom(), Organisme::getInstance(null, 'degustation')->getAdresse(), Organisme::getInstance(null, 'degustation')->getCodePostal(), Organisme::getInstance(null, 'degustation')->getCommune(), Organisme::getInstance(null, 'degustation')->getTelephone(), Organisme::getInstance(null, 'degustation')->getEmail());
    }

    protected function getHeaderSubtitle() {
        $header_subtitle = sprintf("%s\n\n", $this->declaration->declarant->nom);
        $date_validation = DateTimeImmutable::createFromFormat('Y-m-d', $this->declaration->validation);
        $date_validation_odg = DateTimeImmutable::createFromFormat('Y-m-d', $this->declaration->validation_odg);

        if (! $this->declaration->isPapier()) {
            if ($date_validation === false) {
                $header_subtitle .= sprintf("Exemplaire brouillon");
            } else {
                $header_subtitle .= sprintf("Signé électroniquement via l'application de télédéclaration le %s", $date_validation->format('d/m/Y'));

                if ($this->declaration->isNonConformite() === false) {
                    if($date_validation_odg !== false) {
                        $header_subtitle .= ", validée par l'ODG le ".$date_validation_odg->format('d/m/Y');
                    } else {
                        $header_subtitle .= ", en attente de l'approbation par l'ODG";
                    }
                }
            }
        }

        if ($this->declaration->isPapier() && $date_validation !== false) {
            $header_subtitle .= sprintf("Reçue le %s", $date_validation->format('d/m/Y'));
        }

        return $header_subtitle;
    }

    protected function getLogo() {
        foreach($this->declaration->getRegions()  as $r) {
            return 'logo_'.strtolower($r).'.jpg';
        }
        return 'logo_'.strtolower(Organisme::getCurrentOrganisme()).'.jpg';
    }

    protected function getConfig() {
        return new ExportPMCPDFConfig();
    }
}
