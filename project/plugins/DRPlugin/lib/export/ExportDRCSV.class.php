<?php

class ExportDRCSV implements InterfaceDeclarationExportCsv 
{
    protected $doc = null;
    protected $header = false;

    public static function getHeaderCsv() 
    {
        return DouaneCsvFile::CSV_ENTETES;
    }

    public function __construct($doc, $header = true) 
    {
        $this->doc = $doc;
        $this->header = $header;
    }

    public function getFileName() 
    {
        return $this->doc->_id . '_' . $this->doc->_rev . '.csv';
    }

    public function export() {
        $csv = "";
        if($this->header) {
            $csv .= self::getHeaderCsv();
        }
        if ($this->doc->exist('donnees') && count($this->doc->donnees) >= 1) {
          $c = new DRDouaneCsvFile(null, $this->doc);
        	$csv .= $c->convertByDonnees($this->doc);
        } elseif ($file = $this->doc->getFichier('csv')) {
        	$c = new DRDouaneCsvFile($file, $this->doc);
        	$csv .= $c->convert();
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
