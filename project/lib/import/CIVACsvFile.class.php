<?php

abstract class CIVACsvFile {

    abstract public function updateDRevCepage(DRev $drev);
    abstract public function updateDRevProduitDetail(DRev $drev);

    protected $file = null;
    protected $separator = null;
    protected $csvdata = null;
    protected $ignore = null;

    public function getFileName() {
        return $this->file;
    }

    public function __construct($file, $ignore_first_if_comment = 1) {
        $this->ignore = $ignore_first_if_comment;
        if (!file_exists($file) && !preg_match('/^http/', $file))
          throw new Exception("Cannont access $file");

        $this->file = $file;
        $handle = fopen($this->file, 'r');
        if (!$handle)
          throw new Exception('invalid_file');
        $buffer = fread($handle, 500);
        fclose($handle);
        $buffer = preg_replace('/$[^\n]*\n/', '', $buffer);
        if (!$buffer) {
          throw new Exception('invalid_file');
        }
        if (!preg_match('/("?)[0-9]{10}("?)([,;\t])/', $buffer, $match)) {
          throw new Exception('invalid_csv_file');
        }
        $this->separator = $match[3];
      }


        private static function clean($array) {
          for($i = 0 ; $i < count($array) ; $i++) {
            $array[$i] = preg_replace('/^ +/', '', preg_replace('/ +$/', '', $array[$i]));
          }
          return $array;
        }

        public function getCsv() {
          if ($this->csvdata)
            return $this->csvdata;

          $handler = fopen($this->file, 'r');
          if (!$handler)
            throw new Exception('Cannot open csv file anymore');
          $this->csvdata = array();
          while (($data = fgetcsv($handler, 0, $this->separator)) !== FALSE) {
            $this->csvdata[] = self::clean($data);
          }
          fclose($handler);
          if ($this->ignore && !preg_match('/^\d{10}$/', $this->csvdata[0][0]))
            array_shift($this->csvdata);
          return $this->csvdata;
        }

}
