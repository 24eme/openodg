<?php

class ExportDegustationFicheTablesEchantillonsParTourneePDF extends ExportDeclarationLotsPDF
{
    protected $degustation = null;

    public function __construct($degustation, $type = 'pdf', $use_cache = false, $file_dir = null, $filename = null) {
        $this->degustation = $degustation;
        parent::__construct($degustation, $type, $use_cache, $file_dir, $filename);
    }

    public function create() {
        $lots = $this->degustation->getLotsByOperateurs();

        usort($lots, function ($a, $b) {

            if($a[0]->secteur == $b[0]->secteur) {

                return strcmp($a[0]->declarant_nom, $b[0]->declarant_nom);
            }

            return strcmp($a[0]->secteur, $b[0]->secteur);
        });

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
        sfApplicationConfiguration::getActive()->loadHelpers(array('Partial'));
        try {
            return get_partial('degustation/ficheTablesEchantillonsParDossierPdfHeader', ['degustation' => $this->degustation]);
        } catch (Exception $e) {
            return "Fiche des lots ventilés anonymisés par tournée";
        }
    }

    protected function getHeaderSubtitle()
    {
        sfApplicationConfiguration::getActive()->loadHelpers(array('Partial'));
        try {
            return get_partial('degustation/ficheTablesEchantillonsParDossierPdfHeaderSubtitle', ['degustation' => $this->degustation]);
        } catch (Exception $e) {
            $header_subtitle = sprintf("\nDégustation du %s", $this->degustation->getDateFormat('d/m/Y'));
            $header_subtitle .= sprintf("\n%s", $this->degustation->lieu);
            return $header_subtitle;
        }
    }

    public function getFileName($with_rev = false) {
        $filename = sprintf("fiche_echantillons_table_par_tournee_%s", $this->degustation->_id);
        if ($with_rev) {
            $filename .= '_' . $this->degustation->_rev;
        }
        return $filename . '.pdf';
    }
}
