<?php

class CsvFile 
{

  protected $current_line = 0;
  private $file = null;
  private $separator = null;
  protected $csvdata = null;
  private $ignore = null;
  protected $isFileTmp = false;
  protected $options = null;

  public function getFileName() {
    return $this->file;
  }

  public function __construct($file = null, $options = array()) {
    $this->isFileTmp = false;
    $this->options = $options;
    if (!isset($this->options["ignore_first_if_comment"])) {
      $this->options["ignore_first_if_comment"] = true;
    }
    $this->ignore = $this->options["ignore_first_if_comment"];
    $this->separator = ';';
    if (!$file)
      return ;

    if (preg_match('/^http/', $file)) {
        $fileTmp = stream_get_meta_data(tmpfile())['uri'];
        file_put_contents($fileTmp, file_get_contents($file));
        $this->isFileTmp = true;
        $file = $fileTmp;
    }

    if (!file_exists($file))
      throw new sfException("Cannont access $file");
    $this->file = $file;
    $handle = fopen($this->file, 'r');
    if (!$handle) {
      throw new sfException('unable to open file: '.$this->file);
    }
    $buffer = fread($handle, 500);
    fclose($handle);
    
    $charset = $this->getCharset($file);
    if($charset != 'utf-8'){
        exec('recode '.$charset.'..utf-8 '.$file);
    }
    $buffer = preg_replace('/$[^\n]*\n/', '', $buffer);
    if (!$buffer) {
      throw new sfException('invalid csv file; '.$this->file);
    }

    $virgule = explode(',', $buffer);
    $ptvirgule = explode(';', $buffer);
    $tabulation = explode('\t', $buffer);
    if (count($virgule) > count($ptvirgule) && count($virgule) > count($tabulation))
      $this->separator = ',';
    else if (count($tabulation) > count($ptvirgule))
      $this->separator = '\t';
  }

  public function getCsv()
  {
    if ($this->csvdata) {
      return $this->csvdata;
    }
    $handler = fopen($this->file, 'r');
    if (!$handler) {
      throw new sfException('Cannot open csv file anymore');
    }
    $this->csvdata = array();
    while (($data = fgetcsv($handler, 0, $this->separator, "\"", "\\")) !== FALSE) {
      if (!preg_match('/^#/', $data[0])) {
		$this->csvdata[] = $data;
      }
    }
    fclose($handler);

    if($this->isFileTmp && $this->file) {
        unlink($this->file);
    }
    return $this->csvdata;
  }


  public function getPremierChamp(){
    return ($this->getCsv()[3][0]);
  }

  private function getCharset($file) {
    if(preg_match('/^http/', $file)) {
        $fileTmp = stream_get_meta_data(tmpfile())['uri'];
        file_put_contents($fileTmp, file_get_contents($file));
        $file = $fileTmp;
    }
    $ret = exec('file -i '.$file);
    $charset = substr($ret, strpos($ret,'charset='));
    if(isset($fileTmp) && $fileTmp) {
        unlink($fileTmp);
    }
    return str_replace('charset=','',$charset);
  }

    public function __destruct() {
        if($this->isFileTmp && $this->file && file_exists($this->file)) {
            unlink($this->file);
        }
    }
}
