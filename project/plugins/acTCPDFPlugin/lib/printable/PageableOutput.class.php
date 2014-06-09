<?php

class PageableOutput {

  protected $filename;
  protected $file_dir;
  protected $config;

  public function __construct($filename = '', $file_dir = null, $config = null) {
    $this->filename = $filename;
    $this->file_dir = $file_dir;
    $this->config = $config;
    $this->init();
  }

  public function addPage($html) {
  }

  public function output() {
  }

  public function isCached() {
  }

  public function removeCache() {
      
      return true;
  }

  public function addHeaders($response) {
  }

  public function generate($no_cache = false) {
  }

  public function getPDF() {
      
      return null;
  }
}

