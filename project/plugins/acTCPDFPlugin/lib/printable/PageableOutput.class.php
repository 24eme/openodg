<?php

class PageableOutput {

  protected $filename;
  protected $file_dir;
  protected $orientation;

  public function __construct($filename = '', $file_dir = null, $orientation = 'P') {
    $this->filename = $filename;
    $this->file_dir = $file_dir;
    $this->orientation = $orientation;
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

}

