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

    protected $parcellaireAffectation = null;
    protected $nomFilter = null;

    public function __construct($parcellaireAffectation, $type = 'pdf', $use_cache = false, $file_dir = null,  $filename = null) {
        $this->parcellaireAffectation = $parcellaireAffectation;
        $this->nomFilter = null;
        if(!$filename) {
            $filename = $this->getFileName(true, true);
        }

        parent::__construct($type, $use_cache, $file_dir, $filename);
    }

    public function create() {

      $dgcs = $this->parcellaireAffectation->getDgc(true);

      $parcellesByDgc = $this->parcellaireAffectation->getGroupedParcelles(true);

      if(count($parcellesByDgc) == 0) {
         $this->printable_document->addPage($this->getPartial('parcellaireAffectation/pdf', array('parcellaireAffectation' =>    $this->parcellaireAffectation, 'parcellesByCommune' => false)));

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
      foreach ($parcellesByDgc as $dgc => $parcelles) {
        $libelleTableau = str_replace("-", " ", $dgc);
        $parcellesByPage = array();
        $currentPage = array();
        if(($unite + $uniteTableau + $uniteLigne) > $uniteParPage) {
            $parcellesByPage[] = $currentPage;
            $currentPage = array();
            $unite = 0;
        }
        $currentPage[$libelleTableau] = array();
        $unite += $uniteTableau;
        ksort($parcelles);
        foreach($parcelles as $parcelle) {
           if(($unite + $uniteLigne) > $uniteParPage) {
               $parcellesByPage[] = $currentPage;
               $currentPage = array();
               $unite = 0;
               $libelleTableau = $parcelle->commune . " (suite)";
               $currentPage[$libelleTableau] = array();
               $unite += $uniteTableau;
           }
           $unite += $uniteLigne;
           $currentPage[$libelleTableau][] = $parcelle;
        }

        if($unite > 0) {
          $parcellesByPage[] = $currentPage;
        }

        if($this->parcellaireAffectation->observations) {
          $unite += $uniteTableauLigne + count(explode("\n", $this->parcellaireAffectation->observations));
        }

        foreach($parcellesByPage as $nbPage => $parcelles) {
            $this->printable_document->addPage($this->getPartial('parcellaireAffectation/pdf', array(
                'parcellaireAffectation' => $this->parcellaireAffectation,
                'parcellesByCommune' => $parcelles,
                'lastPage' => (($nbPage == count($parcellesByPage) - 1) && (($this->parcellaireAffectation->observations && $unite <= $uniteParPage) || !$this->parcellaireAffectation->observations)),
            )));
        }

        if ($this->parcellaireAffectation->observations && $unite > $uniteParPage) {
            $this->printable_document->addPage($this->getPartial('parcellaireAffectation/pdf', array(
                'parcellaireAffectation' => $this->parcellaireAffectation,
                'parcellesByCommune' => $parcellesByPage,
                'lastPage' => true,
            )));
        }
      }
    }

    protected function getHeaderTitle() {
        return sprintf("Déclaration d'affectation parcellaire %s", $this->parcellaireAffectation->campagne);
    }

    protected function getHeaderSubtitle() {
        $header_subtitle = sprintf("%s", $this->parcellaireAffectation->declarant->nom);
        $header_subtitle .= "\n\n";

        if (!$this->parcellaireAffectation->validation) {
            $header_subtitle .= sprintf("Exemplaire brouilllon");
        }else{
            if ($this->parcellaireAffectation->isAuto()) {
                $date = new DateTime($this->parcellaireAffectation->validation);
                $header_subtitle .= sprintf("Générée automatiquement %s", $date->format('d/m/Y'));
            } elseif ($this->parcellaireAffectation->isPapier()) {
                $date = new DateTime($this->parcellaireAffectation->validation);
                $header_subtitle .= sprintf("Reçue le %s", $date->format('d/m/Y'));
            } else {
                $date = new DateTime($this->parcellaireAffectation->validation);
                $header_subtitle .= sprintf("Signé électroniquement via l'application de télédéclaration le %s", $date->format('d/m/Y'));
                if($this->parcellaireAffectation->exist('signataire') && $this->parcellaireAffectation->signataire) {
                    $header_subtitle .= " par " . $this->parcellaireAffectation->signataire;
                }
            }
        }

        return $header_subtitle;
    }

    protected function getConfig() {

        return new ExportParcellaireAffectationPDFConfig();
    }

    public function getFileName($with_rev = false) {

      return self::buildFileName($this->parcellaireAffectation, $with_rev, $this->nomFilter);
    }

    public static function buildFileName($parcellaireAffectation, $with_rev = false, $nomFilter = null) {

        $prefixName = $parcellaireAffectation->getTypeParcellaire()."_%s_%s";
        $filename = sprintf($prefixName, $parcellaireAffectation->identifiant, $parcellaireAffectation->campagne);

        $declarant_nom = strtoupper(KeyInflector::slugify($parcellaireAffectation->declarant->nom));
        $filename .= '_' . $declarant_nom;

        if($nomFilter) {
            $filename .= '_' . strtoupper(KeyInflector::slugify($nomFilter));
        }

        if ($with_rev) {
            $filename .= '_' . $parcellaireAffectation->_rev;
        }

        return $filename . '.pdf';
    }
}
