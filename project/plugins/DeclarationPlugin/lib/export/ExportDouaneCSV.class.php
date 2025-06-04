<?php

class ExportDouaneCSV implements InterfaceDeclarationExportCsv
{
    protected $doc = null;
    protected $header = false;
    protected $region = null;
    protected $drev_produit_filter = null;

    public static function getHeaderCsv()
    {
        return DouaneCsvFile::CSV_ENTETES;
    }

    public function __construct($doc, $header = true, $region = null) {
        $this->doc = $doc;
        $this->header = $header;
        $this->region = $region;
        $this->drev_produit_filter = null;
    }

    public function getFileName() {
        return $this->doc->_id . '_' . $this->doc->_rev . '.csv';
    }

    public function export() {
        $csv = "";
        if($this->header) {
          $csv .= self::getHeaderCsv();
        }
        return $csv;
    }

    public function getCsv() {
        $csv = array();
        $datas = explode(PHP_EOL, $this->export());
        foreach ($datas as $data) {
            if ($data) {
                $csv[] = explode(';', $data);
            }
        }
        return $csv;
    }

    protected function formatFloat($value) {

        return str_replace(".", ",", $value);
    }

    public function setExtraArgs($args) {
        if (isset($args['drev_produit_filter'])) {
            $this->drev_produit_filter = $args['drev_produit_filter'];
        }
    }

}
