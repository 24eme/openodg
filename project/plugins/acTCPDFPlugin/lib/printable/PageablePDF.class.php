<?php

class PageablePDF extends PageableOutput {

    protected $pdf;
    protected $pdf_file;

    protected function init() {
        define('K_PATH_IMAGES', sfConfig::get('sf_web_dir').'/images/pdf/');
        
        // create new PDF document
        $this->pdf = new acTCPDF($this->orientation, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // set document information
        $this->pdf->SetCreator(PDF_CREATOR);
        $this->pdf->SetAuthor('');
        $this->pdf->SetTitle('');
        $this->pdf->SetSubject('');
        $this->pdf->SetKeywords('');

        // set header and footer fonts
        $this->pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '',  PDF_FONT_SIZE_MAIN));
        $this->pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        // set default monospaced font
        $this->pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        //set margins
       
        $this->pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $this->pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $this->pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        //set auto page breaks
        $this->pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        //set image scale factor
        $this->pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

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

        $this->pdf->SetFont(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN);
    }

    public function getPDF() {

        return $this->pdf;
    }

    public function isCached() {
        return file_exists($this->pdf_file);
    }

    public function removeCache() {
        if (file_exists($this->pdf_file))
            return unlink($this->pdf_file);
        return true;
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

