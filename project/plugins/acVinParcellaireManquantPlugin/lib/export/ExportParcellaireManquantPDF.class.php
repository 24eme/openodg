<?php

class ExportParcellaireManquantPDF extends ExportPDF {

    protected $parcellaireManquant = null;
    protected $nomFilter = null;

    public function __construct($parcellaireManquant, $type = 'pdf', $use_cache = false, $file_dir = null,  $filename = null) {
        $this->parcellaireManquant = $parcellaireManquant;
        $this->nomFilter = null;
        if(!$filename) {
            $filename = $this->getFileName(true, true);
        }

        parent::__construct($type, $use_cache, $file_dir, $filename);
    }

    public function create() {

       $parcellesByCommune = $this->parcellaireManquant->declaration->getParcellesByCommune();

       if(count($parcellesByCommune) == 0) {
           $this->printable_document->addPage($this->getPartial('parcellaireManquant/pdf', array('parcellaireManquant' =>    $this->parcellaireManquant, 'parcellesByCommune' => false)));

           return;
       }

       $unite = 0;
       $uniteParPage = 22;
       $uniteTableau = 4;
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

        if($this->parcellaireManquant->observations) {
            $unite += $uniteTableauLigne + count(explode("\n", $this->parcellaireManquant->observations));
        }

        foreach($parcellesByPage as $nbPage => $parcelles) {
            $this->printable_document->addPage($this->getPartial('parcellaireManquant/pdf', array(
                'parcellaireManquant' => $this->parcellaireManquant,
                'parcellesByCommune' => $parcelles,
                'lastPage' => (($nbPage == count($parcellesByPage) - 1) && (($this->parcellaireManquant->observations && $unite <= $uniteParPage) || !$this->parcellaireManquant->observations)),
            )));
        }

        if ($this->parcellaireManquant->observations && $unite > $uniteParPage) {
            $this->printable_document->addPage($this->getPartial('parcellaireManquant/pdf', array(
                'parcellaireManquant' => $this->parcellaireManquant,
                'parcellesByCommune' => array(),
                'lastPage' => true,
            )));
        }
    }

    public function getLogo() {
        foreach($this->parcellaireManquant->getRegions()  as $r) {
            if(is_file($this->getConfig()->path_images.'logo_'.strtolower($r).'.jpg')) {
                return 'logo_'.strtolower($r).'.jpg';
            }
        }
        return 'logo_'.strtolower(Organisme::getCurrentOrganisme()).'.jpg';
    }

    protected function getHeaderTitle() {
        return sprintf("Déclaration de pieds manquants %s", $this->parcellaireManquant->campagne);
    }

    protected function getHeaderSubtitle() {
        $header_subtitle = sprintf("%s", $this->parcellaireManquant->declarant->nom);
        $header_subtitle .= "\n\n";

        if (!$this->parcellaireManquant->isPapier()) {
            if ($this->parcellaireManquant->validation) {
                $date = new DateTime($this->parcellaireManquant->validation);

                $header_subtitle .= sprintf("Signé électroniquement via l'application de télédéclaration le %s", $date->format('d/m/Y'), $this->parcellaireManquant->signataire);
                if($this->parcellaireManquant->exist('signataire') && $this->parcellaireManquant->signataire) {
                    $header_subtitle .= " par " . $this->parcellaireManquant->signataire;
                }
            }else{
                $header_subtitle .= sprintf("Exemplaire brouilllon");
            }
        }

        if ($this->parcellaireManquant->isPapier() && $this->parcellaireManquant->validation && $this->parcellaireManquant->validation !== true) {
            $date = new DateTime($this->parcellaireManquant->validation);
            $header_subtitle .= sprintf("Reçue le %s", $date->format('d/m/Y'));
        }

        return $header_subtitle;
    }

    protected function getConfig() {

        return new ExportParcellaireManquantPDFConfig();
    }

    public function getFileName($with_rev = false) {

      return self::buildFileName($this->parcellaireManquant, $with_rev, $this->nomFilter);
    }

    public static function buildFileName($parcellaireManquant, $with_rev = false, $nomFilter = null) {

        $prefixName = $parcellaireManquant->getTypeParcellaire()."_%s_%s";
        $filename = sprintf($prefixName, $parcellaireManquant->identifiant, $parcellaireManquant->campagne);

        $declarant_nom = strtoupper(KeyInflector::slugify($parcellaireManquant->declarant->nom));
        $filename .= '_' . $declarant_nom;

        if($nomFilter) {
            $filename .= '_' . strtoupper(KeyInflector::slugify($nomFilter));
        }

        if ($with_rev) {
            $filename .= '_' . $parcellaireManquant->_rev;
        }

        return $filename . '.pdf';
    }
}
