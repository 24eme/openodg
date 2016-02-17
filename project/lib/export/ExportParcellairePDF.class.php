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
class ExportParcellairePDF extends ExportPDF {

    protected $parcellaire = null;
    protected $cviFilter = null;
    protected $nomFilter = null;

    public function __construct($parcellaire, $type = 'pdf', $use_cache = false, $file_dir = null,  $filename = null) {
        $this->parcellaire = $parcellaire;
        $this->cviFilter = null;
        $this->nomFilter = null;
        if(!$filename) {
            $filename = $this->getFileName(true, true);
        }

        parent::__construct($type, $use_cache, $file_dir, $filename);
    }

    public function setCviFilter($cvi, $nom = null) {
        $this->cviFilter = $cvi;
        $this->nomFilter = $nom;
    }

    public function create() {
        $this->parcellesByLieux = $this->parcellaire->getParcellesByLieux($this->cviFilter);
        $this->parcellesByLieuxCommuneAndCepage = $this->parcellaire->getParcellesByLieuxCommuneAndCepage($this->cviFilter);

        if(count($this->parcellesByLieux) == 0) {
            $this->printable_document->addPage($this->getPartial('parcellaire/pdfVide', array('parcellaire' => $this->parcellaire)));

            return;
        }
        
        foreach ($this->parcellesByLieux as $lieuHash => $parcellesByLieu) {
            $this->printable_document->addPage($this->getPartial('parcellaire/pdf', array('parcellaire' => $this->parcellaire, 'parcellesByLieu' => $parcellesByLieu, 'cviFilter' => $this->cviFilter)));
        }

        $this->printable_document->addPage($this->getPartial('parcellaire/pdfRecap', array('parcellaire' => $this->parcellaire, 'parcellesByLieuxCommuneAndCepage' => $this->parcellesByLieuxCommuneAndCepage, 'engagement' => !$this->cviFilter)));
        
    }

    protected function getHeaderTitle() {
        if($this->parcellaire->isParcellaireCremant()){
            return sprintf("Déclaration d'affectation parcellaire crémant %s", $this->parcellaire->campagne);
        }
        return sprintf("Déclaration d'affectation parcellaire %s", $this->parcellaire->campagne);
    }

    protected function getHeaderSubtitle() {
        $header_subtitle = sprintf("%s", $this->parcellaire->declarant->nom);
        $header_subtitle .= "\n\n";
        
        if (!$this->parcellaire->isPapier()) {
            if ($this->parcellaire->validation && $this->parcellaire->campagne >= "2015") {
                $date = new DateTime($this->parcellaire->validation);
                $header_subtitle .= sprintf("Signé électroniquement via l'application de télédéclaration le %s", $date->format('d/m/Y'));
            }else{
                $header_subtitle .= sprintf("Exemplaire brouilllon");
            }
        }

        if ($this->parcellaire->isPapier() && $this->parcellaire->validation && $this->parcellaire->validation !== true) {
            $date = new DateTime($this->parcellaire->validation);
            $header_subtitle .= sprintf("Reçue le %s", $date->format('d/m/Y'));
        } 

        return $header_subtitle;        
    }

    protected function getConfig() {

        return new ExportDRevPDFConfig();
    }

    public function getFileName($with_rev = false) {

      return self::buildFileName($this->parcellaire, $with_rev, $this->nomFilter);
    }

    public static function buildFileName($parcellaire, $with_rev = false, $nomFilter = null) {
        
        $prefixName = ($parcellaire->isParcellaireCremant())? "PARCELLAIRE_CREMANT_%s_%s" :"PARCELLAIRE_%s_%s";
        $filename = sprintf($prefixName, $parcellaire->identifiant, $parcellaire->campagne);

        $declarant_nom = strtoupper(KeyInflector::slugify($parcellaire->declarant->nom));
        $filename .= '_' . $declarant_nom;

        if($nomFilter) {
            $filename .= '_' . strtoupper(KeyInflector::slugify($nomFilter));
        }

        if ($with_rev) {
            $filename .= '_' . $parcellaire->_rev;
        }

        return $filename . '.pdf';
    }
}