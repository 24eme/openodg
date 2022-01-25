<?php

class GenericLatex {

  const OUTPUT_TYPE_PDF = 'pdf';
  const OUTPUT_TYPE_LATEX = 'latex';
  
  public function getLatexFile() {
    $fn = $this->getLatexFileName();
    $leFichier = fopen($fn, "w");
    if (!$leFichier) {
      throw new sfException("Cannot write on ".$fn);
    }
    fwrite($leFichier, $this->getLatexFileContents());
    fclose($leFichier);
    $retour = chmod($fn,intval('0660',8));
    return $fn;
  }

  private function getLatexDestinationDir() {
    $latex_dir = sfConfig::get('sf_app_cache_dir')."/latex/";
    if (!file_exists($latex_dir)){
        mkdir($latex_dir, 0770, true);
    }
    return $latex_dir;
  }

  protected function getTEXWorkingDir() {
      $tmp_dir = sfConfig::get('sf_app_cache_dir')."/tmp/";
      if (!file_exists($tmp_dir)){
          mkdir($tmp_dir, 0770, true);
      }
    return $tmp_dir;
  }

  public function generatePDF() {
    $cmdCompileLatex = '/usr/bin/pdflatex -output-directory="'.$this->getTEXWorkingDir().'" -synctex=1 -interaction=nonstopmode "'.$this->getLatexFile().'" 2>&1';
    //$output = array();
    $output = shell_exec($cmdCompileLatex);

    if (!preg_match('/Transcript written/', $output) || preg_match('/Fatal error/', $output)) {
      throw new sfException($output." : ".$cmdCompileLatex);
    }

    $pdfpath = $this->getLatexFileNameWithoutExtention().'.pdf';

    if (!file_exists($pdfpath)) {
      throw new sfException("pdf not created ($pdfpath): ".$output);
    }
    return $pdfpath;
  }

  private function cleanPDF() {
    $file = $this->getLatexFileNameWithoutExtention();
    @unlink($file.'.aux');
    @unlink($file.'.log');
    @unlink($file.'.pdf');
    @unlink($file.'.tex');
    @unlink($file.'.synctex.gz');
  }

  public function getPDFFile() {
    $filename = $this->getLatexDestinationDir().$this->getPublicFileName();
    if(file_exists($filename))
      return $filename;
    $tmpfile = $this->generatePDF();
    if (!file_exists($tmpfile)) {
      throw new sfException("pdf not created :(");
    }
    if (!rename($tmpfile, $filename)) {
      throw new sfException("not possible to rename $tmpfile to $filename");
    }
    $this->cleanPDF();
    return $filename;
  }

  public function getPDFFileContents() {
    return file_get_contents($this->getPDFFile());
  }

  public function echoPDFWithHTTPHeader() {
    $attachement = 'attachment; filename='.$this->getPublicFileName();
    header("content-type: application/pdf\n");
    header("content-length: ".filesize($this->getPDFFile())."\n");
    header("content-disposition: $attachement\n\n");
    echo $this->getPDFFileContents();
  }

  public function echoLatexWithHTTPHeader() {
    $attachement = 'attachment; filename='.$this->getPublicFileName('.tex');
    header("content-type: application/latex\n");
    header("content-length: ".filesize($this->getLatexFile())."\n");
    header("content-disposition: $attachement\n\n");
    echo $this->getLatexFileContents();
  }

  public function echoWithHTTPHeader($type = 'pdf') {
    if ($type == self::OUTPUT_TYPE_LATEX)
      return $this->echoLatexWithHTTPHeader();
    return $this->echoPDFWithHTTPHeader();
  }
 
  public function getLatexFileName() {
    return $this->getLatexFileNameWithoutExtention().'.tex';
  }

  public function getNbPages() {
    throw new sfException("need to be implemented upstream");
  }
  
  public function getLatexFileNameWithoutExtention() {
    throw new sfException("need to be implemented upstream");
  }

  public function getLatexFileContents() {
    throw new sfException("need to be implemented upstream");
  }

  public function getPublicFileName($extention = '.pdf') {
    throw new sfException("need to be implemented upstream");
  }

}