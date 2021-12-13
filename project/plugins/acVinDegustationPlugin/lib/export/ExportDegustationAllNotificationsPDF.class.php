<?php

class ExportDegustationAllNotificationsPDF extends ExportDeclarationLotsPDF {

    protected $degustation = null;
    protected $etablissements = array();

    public function __construct($degustation, $type = 'pdf', $use_cache = false, $file_dir = null, $filename = null) {
        $this->degustation = $degustation;
        parent::__construct($degustation, $type, $use_cache, $file_dir, $filename);
    }

    public function create() {
        $lots = array();
        $etablissements = [];
        foreach ($this->degustation->getLotsDegustables() as $lot) {
            if($lot->isLeurre()) {
                continue;
            }
            if (in_array($lot->declarant_identifiant, $etablissements) === false) {
                $etablissements[$lot->declarant_identifiant] = EtablissementClient::getInstance()->findByIdentifiant($lot->declarant_identifiant);
            }

            if ($lot->conformite == Lot::CONFORMITE_CONFORME) {
                $lots[$lot->declarant_identifiant]['conforme'][] = $lot;
            } elseif ($lot->statut == Lot::STATUT_NONCONFORME) {
                $lots[$lot->declarant_identifiant]['nonconforme'][] = $lot;
            }
        }

        foreach ($lots as $declarant => $lots_declarant) {
            if (isset($lots_declarant['conforme']) && count($lots_declarant['conforme'])) {
                $this->printable_document->addPage($this->getPartial('degustation/degustationConformitePDF', array('degustation' => $this->degustation, 'etablissement' => $etablissements[$declarant], 'lots' => $lots_declarant['conforme'])));
            }

            if (isset($lots_declarant['nonconforme']) && count($lots_declarant['nonconforme'])) {
                foreach ($lots_declarant['nonconforme'] as $lot_nonconforme) {
                    if ($lot_nonconforme->conformite === Lot::CONFORMITE_NONTYPICITE_CEPAGE) {
                        $this->printable_document->addPage($this->getPartial('degustation/degustationNonConformitePDF_typiciteCepage', array('degustation' => $this->degustation, 'etablissement' => $etablissements[$declarant], 'lot' => $lot_nonconforme)));
                    } elseif ($lot_nonconforme->conformite === Lot::CONFORMITE_NONCONFORME_ANALYTIQUE) {
                        $this->printable_document->addPage($this->getPartial('degustation/degustationNonConformitePDF_analytique', array('degustation' => $this->degustation, 'etablissement' => $etablissements[$declarant], 'lot' => $lot_nonconforme)));
                    } else {
                        $this->printable_document->addPage($this->getPartial('degustation/degustationNonConformitePDF_page1', array('degustation' => $this->degustation, 'etablissement' => $etablissements[$declarant], "lot" => $lot_nonconforme)));
                        $this->printable_document->addPage($this->getPartial('degustation/degustationNonConformitePDF_page2', array('degustation' => $this->degustation, 'etablissement' => $etablissements[$declarant], "lot" => $lot_nonconforme )));
                    }
                }
            }
        }
    }

    protected function getHeaderTitle() {

        return "Résultat pour vos lots";
    }

    protected function getHeaderSubtitle() {
        $header_subtitle = sprintf("\nDégustation du %s", $this->degustation->getDateFormat('d/m/Y'));
        return $header_subtitle;
    }


    public function getFileName($with_rev = false) {
        $filename = sprintf("NOTIFICATIONS_%s", $this->degustation->_id);
        if ($with_rev) {
            $filename .= '_' . $this->degustation->_rev;
        }

        return $filename . '.pdf';
    }

}
