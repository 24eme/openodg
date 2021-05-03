<?php

class ExportDegustationConformitePDF extends ExportDeclarationLotsPDF {

    protected $degustation = null;
    protected $etablissement = null;

    public function __construct($degustation,$etablissement, $type = 'pdf', $use_cache = false, $file_dir = null, $filename = null) {
        $this->degustation = $degustation;
        $this->etablissement = $etablissement;

        parent::__construct($degustation,$type, $use_cache, $file_dir, $filename);
    }

    public function create() {
        $lots = array();
        foreach ($this->degustation->getLots() as $lot) {
            if ($lot->declarant_identifiant == $this->etablissement->identifiant && ($lot->conformite == Lot::CONFORMITE_CONFORME || !$lot->conformite) && ($lot->statut == Lot::STATUT_PRELEVE || $lot->statut == Lot::STATUT_CONFORME) ) {
                $lots[] = $lot;
            }
        }
        $footer= sprintf($this->degustation->getNomOrganisme()." — %s", $this->degustation->getLieuNom());
        $this->printable_document->addPage($this->getPartial('degustation/degustationConformitePDF', array("footer" => $footer, 'degustation' => $this->degustation, 'etablissement' => $this->etablissement, 'lots' => $lots)));
      }


    protected function getHeaderTitle() {
        return "Résultats contrôle de vos lots conformes";
    }

    protected function getHeaderSubtitle() {
        $header_subtitle = sprintf("%s\n\n", $this->etablissement->nom);
        $header_subtitle .= sprintf("Dégustation du %s", $this->degustation->getDateFormat('d/m/Y'));
        return $header_subtitle;
    }

    public function getFileName($with_rev = false) {
        $filename = sprintf("CONFORMITE_%s", $this->degustation->_id);
        if ($with_rev) {
            $filename .= '_' . $this->degustation->_rev;
        }

        return $filename . '.pdf';
    }


}
