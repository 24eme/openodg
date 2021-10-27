<?php

class ExportDegustationFicheTablesEchantillonsParDossierPDF extends ExportDeclarationLotsPDF {

    protected $degustation = null;

    public function __construct($degustation, $type = 'pdf', $use_cache = false, $file_dir = null, $filename = null) {
        $this->degustation = $degustation;
        parent::__construct($degustation, $type, $use_cache, $file_dir, $filename);
    }

    public function create() {
        $lots = $this->degustation->getLotsByOperateurs();
        usort($lots, function ($a, $b) {
            return strcmp($a[0]->unique_id.$a[0]->declarant_nom, $b[0]->unique_id.$b[0]->declarant_nom);
        });

        foreach ($lots as $operateur => &$lots_operateur) {
            usort($lots_operateur, function($a, $b) {return strcmp($a->numero_anonymat, $b->numero_anonymat);});
        }

        $lots = array_merge($lots, ['leurres' => $this->degustation->getLeurres()]);

        @$this->printable_document->addPage(
          $this->getPartial('degustation/ficheTablesEchantillonsParDossierPdf',
          array(
            'degustation' => $this->degustation,
            'lots' => $lots
          )
        ));
    }



    protected function getHeaderTitle() {
       return $this->degustation->getNomOrganisme();
    }

    protected function getHeaderSubtitle()
    {
        $header_subtitle = sprintf("%s\n\n%s", $this->degustation->lieu, "Fiche des lots ventilés anonymisés");

        return $header_subtitle;
    }


    public function getFileName($with_rev = false) {
        $filename = sprintf("fiche_echantillons_table_par_dossier_%s", $this->degustation->_id);
        if ($with_rev) {
            $filename .= '_' . $this->degustation->_rev;
        }
        return $filename . '.pdf';
    }


}
