<?php

class ExportPriseDeMoussePDF extends ExportDeclarationLotsPDF {

    protected $prisedemousse = null;
    protected $etablissement = null;
    protected $changement = null;
    protected $total = false;

    public function __construct($prisedemousse, $type = 'pdf', $use_cache = false, $file_dir = null, $filename = null) {
        $this->prisedemousse = $prisedemousse;
        $this->etablissement = $prisedemousse->getEtablissementObject();

        $this->changement = $prisedemousse->getChangementType();
        $this->total = $prisedemousse->isTotal();

        parent::__construct($type, $use_cache, $file_dir, $filename);
    }

    public function create() {
        $this->printable_document->addPage($this->getPartial('prisedemousse/PDF', array('prisedemousse' => $this->prisedemousse, 'etablissement' => $this->etablissement, 'changement' => $this->changement, 'total' => (bool) $this->total)));
      }


    protected function getHeaderTitle() {
       return "Prise de mousse n° ".$this->prisedemousse->numero_archive;
    }


    protected function getHeaderSubtitle() {
        $header_subtitle = sprintf("%s\n%s\n", $this->prisedemousse->declarant->nom, $this->prisedemousse->declarant->email);
        if (!$this->prisedemousse->isPapier() && $this->prisedemousse->validation && $this->prisedemousse->validation !== true) {
            $date = new DateTime($this->prisedemousse->validation);
            $header_subtitle .= sprintf("Signé électroniquement via l'application de télédéclaration le %s", $date->format('d/m/Y'));
            if(!$this->prisedemousse->validation_odg) {
                $header_subtitle .= ", en attente de vérification par l'ODG";
            }

        } elseif(!$this->prisedemousse->isPapier()) {
            $header_subtitle .= sprintf("Exemplaire brouillon");
        }

        if ($this->prisedemousse->isPapier() && $this->prisedemousse->validation && $this->prisedemousse->validation !== true) {
            $date = new DateTime($this->prisedemousse->validation);
            $header_subtitle .= sprintf("Reçue le %s", $date->format('d/m/Y'));
        }
        return $header_subtitle;
    }


    public function getFileName($with_rev = false) {
        $filename = sprintf("PRISEDEMOUSSE_%s", $this->prisedemousse->_id);
        if ($with_rev) {
            $filename .= '_' . $this->prisedemousse->_rev;
        }

        return $filename . '.pdf';
    }

}
