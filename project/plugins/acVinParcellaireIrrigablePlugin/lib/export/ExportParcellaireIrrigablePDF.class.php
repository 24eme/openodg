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

       $parcellesByCommune = $this->parcellaireIrrigable->declaration->getParcellesByCommune();

       if(count($parcellesByCommune) == 0) {
           $this->printable_document->addPage($this->getPartial('parcellaireIrrigable/pdf', array('parcellaireIrrigable' =>    $this->parcellaireIrrigable, 'parcellesByCommune' => false)));

           return;
       }

       $unite = 0;
       $uniteParPage = 23;
       $uniteTableau = 3;
       $uniteLigne = 1;
       $uniteTableauCommentaire = 2;
       $uniteTableauLigne = 0.75;
       $uniteMentionBasDePage = 1;
       $parcellesByPage = array();
       $page = 0;

        $currentPage = array();
        foreach ($parcellesByCommune as $commune => $parcelles) {
            $libelleTableau = $commune;
            if(($unite + $uniteTableau + $uniteLigne) > $uniteParPage) {
                $parcellesByPage[] = $currentPage;
                $currentPage = array();
                $unite = 0;
            }
            $currentPage[$libelleTableau] = array();
            $unite += $uniteTableau;
            foreach($parcelles as $parcelle) {
               if(($unite + $uniteLigne) > $uniteParPage) {
                   $parcellesByPage[] = $currentPage;
                   $currentPage = array();
                   $unite = 0;
                   $libelleTableau = $commune . " (suite)";
                   $currentPage[$libelleTableau] = array();
                   $unite += $uniteTableau;
               }
               $unite += $uniteLigne;
               $currentPage[$libelleTableau][] = $parcelle;
           }
        }

        if($unite > 0) {
            $parcellesByPage[] = $currentPage;
        }

        if($this->parcellaireIrrigable->observations) {
            $unite += $uniteTableauLigne + count(explode("\n", $this->parcellaireIrrigable->observations));
        }

        foreach($parcellesByPage as $nbPage => $parcelles) {
            $this->printable_document->addPage($this->getPartial('parcellaireIrrigable/pdf', array(
                'parcellaireIrrigable' => $this->parcellaireIrrigable,
                'parcellesByCommune' => $parcelles,
                'lastPage' => (($nbPage == count($parcellesByPage) - 1) && (($this->parcellaireIrrigable->observations && $unite <= $uniteParPage) || !$this->parcellaireIrrigable->observations)),
            )));
        }

        if ($this->parcellaireIrrigable->observations && $unite > $uniteParPage) {
            $this->printable_document->addPage($this->getPartial('parcellaireIrrigable/pdf', array(
                'parcellaireIrrigable' => $this->parcellaireIrrigable,
                'parcellesByCommune' => array(),
                'lastPage' => true,
            )));
        }
    }

    protected function getHeaderTitle() {
        return sprintf("Parcellaire Irrigable %s", $this->parcellaireIrrigable->campagne."-".($this->parcellaireIrrigable->campagne + 1));
    }

    protected function getHeaderSubtitle() {
        $header_subtitle = sprintf("%s", $this->parcellaireIrrigable->declarant->nom);
        $header_subtitle .= "\n\n";

        if (!$this->parcellaireIrrigable->isPapier()) {
            if ($this->parcellaireIrrigable->validation) {
                $date = new DateTime($this->parcellaireIrrigable->validation);

                $header_subtitle .= sprintf("Signé électroniquement via l'application de télédéclaration le %s", $date->format('d/m/Y'), $this->parcellaireIrrigable->signataire);
                if($this->parcellaireIrrigable->exist('signataire') && $this->parcellaireIrrigable->signataire) {
                    $header_subtitle .= " par " . $this->parcellaireIrrigable->signataire;
                }
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
