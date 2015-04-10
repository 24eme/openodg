<?php

class PageablePDF extends PageableOutput {

    protected $pdf;
    protected $pdf_file;

    protected function init() {
        $config = $this->config;
        if(!$config) {
            $config = new acTCPDFConfig();
        }

        if($config->path_images) { 
            define('K_PATH_IMAGES', $config->path_images);
        }
        // create new PDF document
        $this->pdf = new acTCPDF($config->orientation, $config->unit, $config->page_format, true, 'UTF-8', false);

        $this->pdf->SetCreator($config->creator);
        $this->pdf->SetAuthor($config->author);
        $this->pdf->SetTitle($config->title);
        $this->pdf->SetSubject($config->subject);
        $this->pdf->SetKeywords($config->keywords);

        $this->pdf->SetHeaderData($config->header_logo, $config->header_logo_width, $config->header_title, $config->header_string);

        // set header and footer fonts
        $this->pdf->setHeaderFont(Array($config->font_name_main, '',  $config->font_size_main));
        $this->pdf->setFooterFont(Array($config->font_name_data, '', $config->font_size_data));

        // set default monospaced font
        $this->pdf->SetDefaultMonospacedFont($config->font_monospaced);
        //set margins
       
        $this->pdf->SetMargins($config->margin_left, $config->margin_top, $config->margin_right);
        $this->pdf->SetHeaderMargin($config->margin_header);
        $this->pdf->SetFooterMargin($config->margin_footer);

        //set auto page breaks
        $this->pdf->SetAutoPageBreak(TRUE, $config->margin_bottom);

        //set image scale factor
        $this->pdf->setImageScale($config->image_scale);

        //set some language-dependent strings
        $this->pdf->setLanguageArray('fra');

        $this->pdf->setFontSubsetting(true);

         /* Defaulf file_dir */
        if (!$this->file_dir) {
            umask(0002);
            $this->file_dir = sfConfig::get('sf_cache_dir').'/pdf/';
            if (!file_exists($this->file_dir)) {
                mkdir($this->file_dir);
            }
        }
        /******************/

        $this->pdf_file = $this->file_dir.$this->filename;

        // set font

        $this->pdf->SetFont($config->font_name, '', $config->font_size);
    }

    public function isCached() {
        return file_exists($this->pdf_file);
    }

    public function removeCache() {
        if (file_exists($this->pdf_file))
            return unlink($this->pdf_file);
        return true;
    }

    public function getFile() {

        return $this->pdf_file;
    }

    public function addHeaders($response) {
        $response->setHttpHeader('Content-Type', 'application/pdf');
        $response->setHttpHeader('Content-disposition', 'attachment; filename="' . basename($this->filename) . '"');
        $response->setHttpHeader('Content-Transfer-Encoding', 'binary');
        $response->setHttpHeader('Content-Length', filesize($this->pdf_file));
        $response->setHttpHeader('Pragma', '');
        $response->setHttpHeader('Cache-Control', 'public');
        $response->setHttpHeader('Expires', '0');
    }

    public function addPage($html) {
        $this->pdf->AddPage();
        $this->pdf->writeHTML($html);
    }

    public function generate($use_cache = false) {
        if (!$use_cache && $this->isCached()) {
            return true;
        } else {
            $this->removeCache();
        }
        $this->pdf->lastPage();
        return $this->pdf->Output($this->pdf_file, 'F');
    }

    public function output() {
        if (!$this->isCached()) {
            $this->generate();
        }
        return file_get_contents($this->pdf_file);
    }

}

