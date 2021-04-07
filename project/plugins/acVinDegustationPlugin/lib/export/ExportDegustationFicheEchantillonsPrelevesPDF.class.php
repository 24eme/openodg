<?php

class ExportDegustationFicheEchantillonsPrelevesPDF extends ExportDeclarationLotsPDF {

    protected $degustation = null;

    public function __construct($degustation, $type = 'pdf', $use_cache = false, $file_dir = null, $filename = null) {
        $this->degustation = $degustation;
        parent::__construct($degustation, $type, $use_cache, $file_dir, $filename);
    }

    public function create() {
        $lots = $this->degustation->getLotsByOperateurs();
        ksort($lots);

        foreach ($lots as $operateur => &$lots_operateur) {
            usort($lots_operateur, function($a, $b) {return strcmp($a->numero_anonymat, $b->numero_anonymat);});
        }

        $lots = array_merge($lots, ['leurres' => $this->degustation->getLeurres()]);

        @$this->printable_document->addPage(
          $this->getPartial('degustation/ficheEchantillonsPrelevesPdf',
          array(
            'degustation' => $this->degustation,
            'lots' => $lots
          )
        ));
    }



    protected function getHeaderTitle() {
       return "Fiche des lots ventilés anonymisés";
    }

    protected function getHeaderSubtitle() {

        $header_subtitle = sprintf("\nDégustation du %s", $this->degustation->getDateFormat('d/m/Y'));
        $header_subtitle .= sprintf("\n%s", $this->degustation->lieu);

        return $header_subtitle;
    }


    public function getFileName($with_rev = false) {
        $filename = sprintf("fiche_echantillons_preleves_%s", $this->degustation->_id);
        if ($with_rev) {
            $filename .= '_' . $this->degustation->_rev;
        }
        return $filename . '.pdf';
    }


}
