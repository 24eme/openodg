<?php

class ExportDegustationFichePresenceDegustateursPDF extends ExportPDF {

    protected $degustation = null;

    public function __construct($degustation, $type = 'pdf', $use_cache = false, $file_dir = null, $filename = null) {
        $this->degustation = $degustation;

        if (!$filename) {
            $filename = $this->getFileName(true);
        }
        parent::__construct($type, $use_cache, $file_dir, $filename);
    }

    public function create() {
        $degustateurs = [];

        foreach ($this->degustation->degustateurs as $college => $degustateurs_college) {
            foreach ($degustateurs_college as $degustateur) {
                $degustateurs[] = [
                    'degustateur' => CompteClient::getInstance()->findByIdentifiant($degustateur->getKey()),
                    'college' => $college,
                    'confirme' => $degustateur->exist('confirmation') && $degustateur->confirmation
                ];
            }
        }

        uasort($degustateurs, function ($d1, $d2) {
            return strcmp($d1['degustateur']->nom, $d2['degustateur']->nom);
        });

        @$this->printable_document->addPage($this->getPartial('degustation/fichePresenceDegustateursPdf', [
            'degustation' => $this->degustation,
            'degustateurs' => $degustateurs
        ]));
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

        $header_subtitle = sprintf("%s\n", $this->degustation->lieu)."Feuille de présence";
        return $header_subtitle;
    }


    protected function getFooterText() {
        $footer= sprintf($this->degustation->getNomOrganisme()." — %s", $this->degustation->getLieuNom());
        return $footer;
    }

    protected function getConfig() {

        return new ExportDegustationFichePresenceDegustateursPDFConfig();
    }

    public function getFileName($with_rev = false) {

        return self::buildFileName($this->degustation, true);
    }

    public static function buildFileName($degustation, $with_rev = false) {
        $filename = sprintf("feuille_de_presence_%s", $degustation->_id);


        if ($with_rev) {
            $filename .= '_' . $degustation->_rev;
        }


        return $filename . '.pdf';
    }

}
