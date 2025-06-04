<?php 

require_once(dirname(__FILE__).'/vendor/tcpdf/config/tcpdf_config.php');

class acTCPDFConfig
{
    const ORIENTATION_PORTRAIT = 'P';
    const ORIENTATION_LANDSCAPE = 'L';

    const PAGE_FORMAT = 'A4';

    public $author = null;
    public $creator = null;
    public $font_monospaced = null;
    public $font_name_data = null;
    public $font_name = null;
    public $font_name_main = null;
    public $font_size_data = null;
    public $font_size = null;
    public $font_size_main = null;
    public $footer_text = null;
    public $header_enabled = null;
    public $header_logo = null;
    public $header_logo_width = null;
    public $header_string = null;
    public $header_title = null;
    public $image_scale = null;
    public $keywords = null;
    public $margin_bottom = null;
    public $margin_footer = null;
    public $margin_header = null;
    public $margin_left = null;
    public $margin_right = null;
    public $margin_top = null;
    public $orientation = null;
    public $page_format = null;
    public $path_images = null;
    public $subject = null;
    public $title = null;
    public $unit = null;



    public function __construct() {
        $this->orientation = PDF_PAGE_ORIENTATION;
        $this->page_format = PDF_PAGE_FORMAT;
        $this->unit = PDF_UNIT;
        $this->path_images = null;
        $this->font_name_main = PDF_FONT_NAME_MAIN;
        $this->font_size_main = PDF_FONT_SIZE_MAIN;
        $this->font_name_data = PDF_FONT_NAME_DATA;
        $this->font_size_data = PDF_FONT_SIZE_DATA;
        $this->font_monospaced = PDF_FONT_MONOSPACED;
        $this->margin_left = PDF_MARGIN_LEFT;
        $this->margin_top = PDF_MARGIN_TOP;
        $this->margin_right = PDF_MARGIN_RIGHT;
        $this->margin_bottom = PDF_MARGIN_BOTTOM;
        $this->margin_header = PDF_MARGIN_HEADER;
        $this->margin_footer = PDF_MARGIN_FOOTER;
        $this->image_scale = PDF_IMAGE_SCALE_RATIO;
        $this->font_name = PDF_FONT_NAME_MAIN;
        $this->font_size = PDF_FONT_SIZE_MAIN;
        $this->header_logo = K_BLANK_IMAGE;
        $this->header_logo_width = 0;
        $this->header_title = PDF_HEADER_TITLE;
        $this->header_string = PDF_HEADER_STRING;
        $this->footer_text = '';
        $this->creator = PDF_CREATOR;
        $this->author = PDF_AUTHOR;
        $this->title = '';
        $this->subject = '';
        $this->keywords = '';
        $this->header_enabled = true;
    }
}
