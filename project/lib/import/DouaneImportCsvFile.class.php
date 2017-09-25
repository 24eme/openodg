<?php

class DouaneImportCsvFile {

    protected $filePath = null;
    protected $campagne = null;

    public function __construct($filePath, $campagne = null) {
        $this->filePath = $filePath;
        $this->campagne = ($campagne)? $campagne : date('Y');
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
}
