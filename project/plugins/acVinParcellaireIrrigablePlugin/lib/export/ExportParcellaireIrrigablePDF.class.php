<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ExportParcellairePdf
 *
 * @author mathurin
 */
class ExportParcellaireIrrigablePDF extends ExportPDF {

    protected $parcellaireIrrigable = null;
    protected $nomFilter = null;

    public function __construct($parcellaireIrrigable, $type = 'pdf', $use_cache = false, $file_dir = null,  $filename = null) {
        $this->parcellaireIrrigable = $parcellaireIrrigable;
        $this->nomFilter = null;
        if(!$filename) {
            $filename = $this->getFileName(true, true);
        }

        parent::__construct($type, $use_cache, $file_dir, $filename);
    }

    public function create() {
    	
       $this->parcellesIrrigableForDetails = $this->parcellaireIrrigable->declaration->getParcellesByCommune();
       
       if(count($this->parcellesIrrigableForDetails) == 0) {
       		$this->printable_document->addPage($this->getPartial('parcellaireIrrigable/pdfVide', array('parcellaireIrrigable' => $this->parcellaireIrrigable)));
       		return;
       }
       
       foreach ($this->parcellesIrrigableForDetails as $commune => $parcellesForDetail) {
       		$this->printable_document->addPage($this->getPartial('parcellaireIrrigable/pdf', array('parcellaireIrrigable' => $this->parcellaireIrrigable, 'parcellesForDetail' => $parcellesForDetail, 'titre' => $commune)));
       }
    }

    protected function getHeaderTitle() {
        return sprintf("Déclaration d'intention de parcelles irrigables %s", $this->parcellaireIrrigable->campagne);
    }

    protected function getHeaderSubtitle() {
        $header_subtitle = sprintf("%s", $this->parcellaireIrrigable->declarant->nom);
        $header_subtitle .= "\n\n";

        if (!$this->parcellaireIrrigable->isPapier()) {
            if ($this->parcellaireIrrigable->validation) {
                $date = new DateTime($this->parcellaireIrrigable->validation);
                $header_subtitle .= sprintf("Signé électroniquement via l'application de télédéclaration le %s", $date->format('d/m/Y'));
            }else{
                $header_subtitle .= sprintf("Exemplaire brouilllon");
            }
        }

        if ($this->parcellaireIrrigable->isPapier() && $this->parcellaireIrrigable->validation && $this->parcellaireIrrigable->validation !== true) {
            $date = new DateTime($this->parcellaireIrrigable->validation);
            $header_subtitle .= sprintf("Reçue le %s", $date->format('d/m/Y'));
        }

        return $header_subtitle;
    }

    protected function getConfig() {

        return new ExportParcellaireIrrigablePDFConfig();
    }

    public function getFileName($with_rev = false) {

      return self::buildFileName($this->parcellaireIrrigable, $with_rev, $this->nomFilter);
    }

    public static function buildFileName($parcellaireIrrigable, $with_rev = false, $nomFilter = null) {

        $prefixName = $parcellaireIrrigable->getTypeParcellaire()."_%s_%s";
        $filename = sprintf($prefixName, $parcellaireIrrigable->identifiant, $parcellaireIrrigable->campagne);

        $declarant_nom = strtoupper(KeyInflector::slugify($parcellaireIrrigable->declarant->nom));
        $filename .= '_' . $declarant_nom;

        if($nomFilter) {
            $filename .= '_' . strtoupper(KeyInflector::slugify($nomFilter));
        }

        if ($with_rev) {
            $filename .= '_' . $parcellaireIrrigable->_rev;
        }

        return $filename . '.pdf';
    }
}
