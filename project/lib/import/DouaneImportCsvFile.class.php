<?php

class DouaneImportCsvFile {

    protected $filePath = null;
    protected $doc = null;
    protected $campagne = null;
    protected $configuration = null;

    public function __construct($filePath, $doc = null) {
        $this->filePath = $filePath;
        $this->doc = $doc;
        $this->campagne = ($doc)? $doc->campagne : date('Y');
        $this->configuration = ConfigurationClient::getConfiguration();
    }

    public static function clean($array) {
      for($i = 0 ; $i < count($array) ; $i++) {
        $array[$i] = preg_replace("/[ ]+/", " ", preg_replace('/^ +/', '', preg_replace('/ +$/', '', $array[$i])));
      }
      return $array;
    }

    public static function numerizeVal($val, $nbDecimal = 2) {
    	return (is_numeric($val))? str_replace('.', ',', sprintf('%01.'.$nbDecimal.'f', $val)) : $val;
    }

    public static function cleanStr($val) {
    	return str_replace(array("\r", "\r\n", "\n"), ' ', $val);
    }

    public static function getNewInstanceFromType($type, $file, $campagne = null)  {
        switch ($type) {
            case 'DR':
                return new DRDouaneCsvFile($file, $campagne);
            case 'SV11':
                return new SV11DouaneCsvFile($file, $campagne);
            case 'SV12':
                return new SV12DouaneCsvFile($file, $campagne);
        }

        return null;
    }
}
