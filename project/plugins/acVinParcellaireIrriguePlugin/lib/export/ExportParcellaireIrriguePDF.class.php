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
class ExportParcellaireIrriguePDF extends ExportPDF {

    protected $parcellaireIrrigue = null;
    protected $nomFilter = null;

    public function __construct($parcellaireIrrigue, $type = 'pdf', $use_cache = false, $file_dir = null,  $filename = null) {
        $this->parcellaireIrrigue = $parcellaireIrrigue;
        $this->nomFilter = null;
        if(!$filename) {
            $filename = $this->getFileName(true, true);
        }

        parent::__construct($type, $use_cache, $file_dir, $filename);
    }

    public function create() {

       $parcellesByCommune = $this->parcellaireIrrigue->declaration->getParcellesByCommune();

       if(count($parcellesByCommune) == 0) {
           $this->printable_document->addPage($this->getPartial('parcellaireIrrigue/pdf', array('parcellaireIrrigue' =>    $this->parcellaireIrrigue, 'parcellesByCommune' => false)));

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

        if($this->parcellaireIrrigue->observations) {
            $unite += $uniteTableauLigne + count(explode("\n", $this->parcellaireIrrigue->observations));
        }

        foreach($parcellesByPage as $nbPage => $parcelles) {
            $this->printable_document->addPage($this->getPartial('parcellaireIrrigue/pdf', array(
                'parcellaireIrrigue' => $this->parcellaireIrrigue,
                'parcellesByCommune' => $parcelles,
                'lastPage' => (($nbPage == count($parcellesByPage) - 1) && (($this->parcellaireIrrigue->observations && $unite <= $uniteParPage) || !$this->parcellaireIrrigue->observations)),
            )));
        }

        if ($this->parcellaireIrrigue->observations && $unite > $uniteParPage) {
            $this->printable_document->addPage($this->getPartial('parcellaireIrrigue/pdf', array(
                'parcellaireIrrigue' => $this->parcellaireIrrigue,
                'parcellesByCommune' => array(),
                'lastPage' => true,
            )));
        }
    }

    protected function getHeaderTitle() {
        $date = new DateTime($this->parcellaireIrrigue->date);
        return sprintf("Déclaration d'irrigation %s",$date->format('d/m/Y'));
    }

    protected function getHeaderSubtitle() {
        $header_subtitle = sprintf("%s", $this->parcellaireIrrigue->declarant->nom);
        $header_subtitle .= "\n\n";

        if (!$this->parcellaireIrrigue->isPapier()) {
            if ($this->parcellaireIrrigue->validation) {
                $date = new DateTime($this->parcellaireIrrigue->validation);

                $header_subtitle .= sprintf("Signé électroniquement via l'application de télédéclaration le %s", $date->format('d/m/Y'), $this->parcellaireIrrigue->signataire);
                if($this->parcellaireIrrigue->exist('signataire') && $this->parcellaireIrrigue->signataire) {
                    $header_subtitle .= " par " . $this->parcellaireIrrigue->signataire;
                }
            }else{
                $header_subtitle .= sprintf("Exemplaire brouilllon");
            }
        }

        if ($this->parcellaireIrrigue->isPapier() && $this->parcellaireIrrigue->validation && $this->parcellaireIrrigue->validation !== true) {
            $date = new DateTime($this->parcellaireIrrigue->validation);
            $header_subtitle .= sprintf("Reçue le %s", $date->format('d/m/Y'));
        }

        return $header_subtitle;
    }

    protected function getConfig() {

        return new ExportParcellaireIrriguePDFConfig();
    }

    public function getFileName($with_rev = false) {

      return self::buildFileName($this->parcellaireIrrigue, $with_rev, $this->nomFilter);
    }

    public static function buildFileName($parcellaireIrrigue, $with_rev = false, $nomFilter = null) {

        $prefixName = $parcellaireIrrigue->getTypeParcellaire()."_%s_%s";
        $filename = sprintf($prefixName, $parcellaireIrrigue->identifiant, $parcellaireIrrigue->campagne);

        $declarant_nom = strtoupper(KeyInflector::slugify($parcellaireIrrigue->declarant->nom));
        $filename .= '_' . $declarant_nom;

        if($nomFilter) {
            $filename .= '_' . strtoupper(KeyInflector::slugify($nomFilter));
        }

        if ($with_rev) {
            $filename .= '_' . $parcellaireIrrigue->_rev;
        }

        return $filename . '.pdf';
    }
}
