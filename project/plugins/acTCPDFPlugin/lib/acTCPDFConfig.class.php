<?php 

require_once(dirname(__FILE__).'/vendor/tcpdf/config/tcpdf_config.php');

class acTCPDFConfig
{
    const ORIENTATION_PORTRAIT = 'P';
    const ORIENTATION_LANDSCAPE = 'L';

    const PAGE_FORMAT = 'A4';

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
        $this->creator = PDF_CREATOR;
        $this->author = PDF_AUTHOR;
        $this->title = '';
        $this->subject = '';
        $this->keywords = '';
    }
}