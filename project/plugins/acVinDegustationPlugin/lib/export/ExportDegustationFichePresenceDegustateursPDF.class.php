<?php

class ExportDegustationFichePresenceDegustateursPDF extends ExportDeclarationLotsPDF {

    protected $degustation = null;

    public function __construct($degustation, $type = 'pdf', $use_cache = false, $file_dir = null, $filename = null) {
        $this->degustation = $degustation;
        parent::__construct($degustation,$type, $use_cache, $file_dir, $filename);
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



    protected function getHeaderTitle() {
        return "Feuille de présence";
    }

    protected function getHeaderSubtitle() {
        $header_subtitle = sprintf("\nDégustation du %s", $this->degustation->getDateFormat('d/m/Y'));
        $header_subtitle .= sprintf("\n%s", $this->degustation->lieu);

        return $header_subtitle;
    }



    public function getFileName($with_rev = false) {
        $filename = sprintf("Feuille_de_presence_%s", $this->degustation->_id);
        if ($with_rev) {
            $filename .= '_' . $this->degustation->_rev;
        }

        return $filename . '.pdf';
    }


}
