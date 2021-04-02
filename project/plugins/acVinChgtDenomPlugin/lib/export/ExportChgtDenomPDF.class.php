<?php

class ExportChgtDenomPDF extends ExportDeclarationLotsPDF {

    protected $chgtdenom = null;
    protected $etablissement = null;
    protected $changement = null;
    protected $total = false;

    public function __construct($chgtdenom, $type = 'pdf', $use_cache = false, $file_dir = null, $filename = null) {
        $this->chgtdenom = $chgtdenom;
        $this->etablissement = $chgtdenom->getEtablissementObject();

        $this->changement = $chgtdenom->getChangementType();
        $this->total = $chgtdenom->isTotal();

        parent::__construct($type, $use_cache, $file_dir, $filename);
    }

    public function create() {
        $this->printable_document->addPage($this->getPartial('chgtdenom/PDF', array('chgtdenom' => $this->chgtdenom, 'etablissement' => $this->etablissement, 'courrierInfos' => $this->courrierInfos, 'changement' => $this->changement, 'total' => (bool) $this->total)));
      }


    protected function getHeaderTitle() {
       return "Changement de dénomination";
    }


    protected function getHeaderSubtitle() {
        $header_subtitle = sprintf("%s\n\n", $this->chgtdenom->declarant->nom);
        if (!$this->chgtdenom->isPapier() && $this->chgtdenom->validation && $this->chgtdenom->validation !== true) {
            $date = new DateTime($this->chgtdenom->validation);
            $header_subtitle .= sprintf("Signé électroniquement via l'application de télédéclaration le %s", $date->format('d/m/Y'));
            if($this->chgtdenom->validation_odg) {
                $dateOdg = new DateTime($this->chgtdenom->validation_odg);
                $header_subtitle .= ", validée par l'ODG le ".$dateOdg->format('d/m/Y');
            } else {
                $header_subtitle .= ", en attente de l'approbation par l'ODG";
            }

        } elseif(!$this->chgtdenom->isPapier()) {
            $header_subtitle .= sprintf("Exemplaire brouillon");
        }

        if ($this->chgtdenom->isPapier() && $this->chgtdenom->validation && $this->chgtdenom->validation !== true) {
            $date = new DateTime($this->chgtdenom->validation);
            $header_subtitle .= sprintf("Reçue le %s", $date->format('d/m/Y'));
        }
        return $header_subtitle;
    }


    public function getFileName($with_rev = false) {
        $filename = sprintf("DECLASSEMENT_%s", $this->chgtdenom->_id);
        if ($with_rev) {
            $filename .= '_' . $this->chgtdenom->_rev;
        }

        return $filename . '.pdf';
    }

}
