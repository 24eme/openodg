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
class ExportParcellaireAffectationPDF extends ExportPDF {

    protected $ParcellaireAffectation = null;
    protected $nomFilter = null;

    public function __construct($ParcellaireAffectation, $type = 'pdf', $use_cache = false, $file_dir = null,  $filename = null) {
        $this->ParcellaireAffectation = $ParcellaireAffectation;
        $this->nomFilter = null;
        if(!$filename) {
            $filename = $this->getFileName(true, true);
        }

        parent::__construct($type, $use_cache, $file_dir, $filename);
    }

    public function create() {

       $parcellesByCommune = $this->ParcellaireAffectation->declaration->getParcellesByCommune();

       if(count($parcellesByCommune) == 0) {
           $this->printable_document->addPage($this->getPartial('ParcellaireAffectation/pdf', array('ParcellaireAffectation' =>    $this->ParcellaireAffectation, 'parcellesByCommune' => false)));

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

        if($this->ParcellaireAffectation->observations) {
            $unite += $uniteTableauLigne + count(explode("\n", $this->ParcellaireAffectation->observations));
        }

        foreach($parcellesByPage as $nbPage => $parcelles) {
            $this->printable_document->addPage($this->getPartial('ParcellaireAffectation/pdf', array(
                'ParcellaireAffectation' => $this->ParcellaireAffectation,
                'parcellesByCommune' => $parcelles,
                'lastPage' => (($nbPage == count($parcellesByPage) - 1) && (($this->ParcellaireAffectation->observations && $unite <= $uniteParPage) || !$this->ParcellaireAffectation->observations)),
            )));
        }

        if ($this->ParcellaireAffectation->observations && $unite > $uniteParPage) {
            $this->printable_document->addPage($this->getPartial('ParcellaireAffectation/pdf', array(
                'ParcellaireAffectation' => $this->ParcellaireAffectation,
                'parcellesByCommune' => array(),
                'lastPage' => true,
            )));
        }
    }

    protected function getHeaderTitle() {
        $date = new DateTime($this->ParcellaireAffectation->date);
        return sprintf("Déclaration d'irrigation %s",$date->format('d/m/Y'));
    }

    protected function getHeaderSubtitle() {
        $header_subtitle = sprintf("%s", $this->ParcellaireAffectation->declarant->nom);
        $header_subtitle .= "\n\n";

        if (!$this->ParcellaireAffectation->isPapier()) {
            if ($this->ParcellaireAffectation->validation) {
                $date = new DateTime($this->ParcellaireAffectation->validation);

                $header_subtitle .= sprintf("Signé électroniquement via l'application de télédéclaration le %s", $date->format('d/m/Y'), $this->ParcellaireAffectation->signataire);
                if($this->ParcellaireAffectation->exist('signataire') && $this->ParcellaireAffectation->signataire) {
                    $header_subtitle .= " par " . $this->ParcellaireAffectation->signataire;
                }
            }else{
                $header_subtitle .= sprintf("Exemplaire brouilllon");
            }
        }

        if ($this->ParcellaireAffectation->isPapier() && $this->ParcellaireAffectation->validation && $this->ParcellaireAffectation->validation !== true) {
            $date = new DateTime($this->ParcellaireAffectation->validation);
            $header_subtitle .= sprintf("Reçue le %s", $date->format('d/m/Y'));
        }

        return $header_subtitle;
    }

    protected function getConfig() {

        return new ExportParcellaireAffectationPDFConfig();
    }

    public function getFileName($with_rev = false) {

      return self::buildFileName($this->ParcellaireAffectation, $with_rev, $this->nomFilter);
    }

    public static function buildFileName($ParcellaireAffectation, $with_rev = false, $nomFilter = null) {

        $prefixName = $ParcellaireAffectation->getTypeParcellaire()."_%s_%s";
        $filename = sprintf($prefixName, $ParcellaireAffectation->identifiant, $ParcellaireAffectation->campagne);

        $declarant_nom = strtoupper(KeyInflector::slugify($ParcellaireAffectation->declarant->nom));
        $filename .= '_' . $declarant_nom;

        if($nomFilter) {
            $filename .= '_' . strtoupper(KeyInflector::slugify($nomFilter));
        }

        if ($with_rev) {
            $filename .= '_' . $ParcellaireAffectation->_rev;
        }

        return $filename . '.pdf';
    }
}
