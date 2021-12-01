<?php

class ExportDegustationEtiquettesTablesEchantillonsParAnonymatOrUniqueidPDF extends ExportPDF {

    protected $degustation = null;
    const MAX_PLANCHE = 24;

    const TRI_NUMERO_ANONYMAT = 'numero_anonymat';
    const TRI_UNIQUE_ID = 'unique_id';

    public function __construct($degustation, $type = 'pdf', $tri = 'numero_anonymat', $use_cache = false, $file_dir = null, $filename = null) {
        $this->degustation = $degustation;

        if ($tri == self::TRI_NUMERO_ANONYMAT) {
            $this->tri = $tri;
        }elseif ($tri == self::TRI_UNIQUE_ID) {
            $this->tri = $tri;
        }else{
            throw new sfException('tri doit être soit '.self::TRI_NUMERO_ANONYMAT.' ou '.self::TRI_UNIQUE_ID);
        }

        if (!$filename) {
            $filename = $this->getFileName(true);
        }
        parent::__construct($type, $use_cache, $file_dir, $filename);
        if($this->printable_document->getPdf()){
          $this->printable_document->getPdf()->setPrintHeader(false);
          $this->printable_document->getPdf()->setPrintFooter(false);
        }
    }

    public function create() {
        $i = 0;
        $plancheLots = [];
        $lots = $this->degustation->getLotsDegustables();

        foreach($lots as $lot) {
            if (!$lot->unique_id) {
                $lot->unique_id = '9999-9999-99999-99999';
            }
        }

        if ($this->tri == self::TRI_NUMERO_ANONYMAT) {
            usort($lots, function ($a, $b) {
                return strcmp($a->numero_anonymat, $b->numero_anonymat);
            });
        }elseif ($this->tri == self::TRI_UNIQUE_ID) {
            usort($lots, function ($a, $b) {
                return strcmp($a->unique_id, $b->unique_id);
            });
        }

        foreach ($lots as $lot) {
            $plancheLots[] = $lot;
            $i++;

            if ($i == self::MAX_PLANCHE) {
                $this->printable_document->addPage($this->getPartial('degustation/etiquettesTablesEchantillonsParAnonymatPDF', array('degustation' => $this->degustation, 'plancheLots' => $plancheLots)));
                $i = 0;
                $plancheLots = [];
            }
        }
        $this->printable_document->addPage($this->getPartial('degustation/etiquettesTablesEchantillonsParAnonymatPDF', array('degustation' => $this->degustation, 'plancheLots' => $plancheLots)));
  }


    public function output() {
        if($this->printable_document instanceof PageableHTML) {
            return parent::output();
        }

        return file_get_contents($this->getFile());
    }

    public function getFile() {

        if($this->printable_document instanceof PageableHTML) {
            return parent::getFile();
        }

        return sfConfig::get('sf_cache_dir').'/pdf/'.$this->getFileName(true);
    }

    protected function getHeaderTitle() {
        return "";
    }

    protected function getFooterText() {
        return "";
    }

    protected function getHeaderSubtitle() {

        return "";
    }


    protected function getConfig() {

        return new ExportDegustationEtiquettesPDFConfig();
    }

    public function getFileName($with_rev = false) {

        return self::buildFileName($this->degustation, $this->tri, true);
    }

    public static function buildFileName($degustation, $tri = 'numero_anonymat', $with_rev = false) {
        if ($tri == self::TRI_UNIQUE_ID) {
            $filename = sprintf("etiquettes_des_lots_par_dossier_%s", $degustation->_id);
        }else{
            $filename = sprintf("etiquettes_des_lots_par_anonymat_%s", $degustation->_id);
        }


        if ($with_rev) {
            $filename .= '_' . $degustation->_rev;
        }


        return $filename . '.pdf';
    }

}
