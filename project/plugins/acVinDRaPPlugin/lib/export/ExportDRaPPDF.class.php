<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ExportDRaPPdf
 *
 * @author mathurin
 */
class ExportDRaPPDF extends ExportPDF {

    protected $drap = null;
    protected $nomFilter = null;

    public function __construct($drap, $type = 'pdf', $use_cache = false, $file_dir = null,  $filename = null) {
        $this->drap = $drap;
        $this->nomFilter = null;
        if(!$filename) {
            $filename = $this->getFileName(true, true);
        }

        parent::__construct($type, $use_cache, $file_dir, $filename);
    }

    public function create() {

       $parcellesByCommune = $this->drap->declaration->getParcellesByCommune();

       if(count($parcellesByCommune) == 0) {
           $this->printable_document->addPage($this->getPartial('drap/pdf', array('drap' =>    $this->drap, 'parcellesByCommune' => false)));

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

        if($this->drap->observations) {
            $unite += $uniteTableauLigne + count(explode("\n", $this->drap->observations));
        }

        foreach($parcellesByPage as $nbPage => $parcelles) {
            $this->printable_document->addPage($this->getPartial('drap/pdf', array(
                'drap' => $this->drap,
                'parcellesByCommune' => $parcelles,
                'lastPage' => (($nbPage == count($parcellesByPage) - 1) && (($this->drap->observations && $unite <= $uniteParPage) || !$this->drap->observations)),
            )));
        }

        if ($this->drap->observations && $unite > $uniteParPage) {
            $this->printable_document->addPage($this->getPartial('drap/pdf', array(
                'drap' => $this->drap,
                'parcellesByCommune' => array(),
                'lastPage' => true,
            )));
        }
    }

    protected function getHeaderTitle() {

        return sprintf("Déclaration de Renonciation à Produire %s", $this->drap->campagne."-".(intval($this->drap->campagne) + 1));
    }

    protected function getHeaderSubtitle() {
        $header_subtitle = sprintf("%s", $this->drap->declarant->nom);
        $header_subtitle .= "\n\n";

        if (!$this->drap->isPapier()) {
            if (!$this->drap->validation) {
                $header_subtitle .= sprintf("Exemplaire brouilllon");
            }elseif($this->drap->isAuto()) {
                $date = new DateTime($this->drap->validation);
                $header_subtitle .= sprintf("Générée automatiquement le %s", $date->format('d/m/Y'));
            }elseif($this->drap->isPapier()) {
                $date = new DateTime($this->drap->validation);
                $header_subtitle .= sprintf("Reçue le %s", $date->format('d/m/Y'));
            }else {
                 $date = new DateTime($this->drap->validation);
                $header_subtitle .= sprintf("Signé électroniquement via l'application de télédéclaration le %s", $date->format('d/m/Y'), $this->drap->signataire);
                if($this->drap->exist('signataire') && $this->drap->signataire) {
                    $header_subtitle .= " par " . $this->drap->signataire;
                }
            }
        }

        if ($this->drap->isPapier() && $this->drap->validation && $this->drap->validation !== true) {
        }

        return $header_subtitle;
    }

    protected function getConfig() {

        return new ExportDRaPPDFConfig();
    }

    public function getFileName($with_rev = false) {

      return self::buildFileName($this->drap, $with_rev, $this->nomFilter);
    }

    public static function buildFileName($drap, $with_rev = false, $nomFilter = null) {

        $prefixName = $drap->getTypeParcellaire()."_%s_%s";
        $filename = sprintf($prefixName, $drap->identifiant, $drap->campagne);

        $declarant_nom = strtoupper(KeyInflector::slugify($drap->declarant->nom));
        $filename .= '_' . $declarant_nom;

        if($nomFilter) {
            $filename .= '_' . strtoupper(KeyInflector::slugify($nomFilter));
        }

        if ($with_rev) {
            $filename .= '_' . $drap->_rev;
        }

        return $filename . '.pdf';
    }
}
