<?php

class ExportDouaneCSV implements InterfaceDeclarationExportCsv
{
    protected $doc = null;
    protected $header = false;
    protected $region = null;
    protected $extraFields = false;

    public static function getHeaderCsv()
    {
        return DouaneCsvFile::CSV_ENTETES;
    }

    public static function getExtraFieldsHeaderCsv()
    {
        return "Organisme;Doc Id;;Hash produit\n";
    }

    public function __construct($doc, $header = true, $region = null, $extraFields = false) {
        $this->doc = $doc;
        $this->header = $header;
        $this->region = $region;
        $this->extraFields = $extraFields;
    }

    public function getFileName() {
        return $this->doc->_id . '_' . $this->doc->_rev . '.csv';
    }

    public function export() {
        $csv = "";
        if($this->header) {
          if($this->extraFields) {
              $csv .= str_replace(PHP_EOL,"", self::getHeaderCsv()).";".self::getExtraFieldsHeaderCsv();
          } else {
            $csv .= self::getHeaderCsv();
          }
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
}
