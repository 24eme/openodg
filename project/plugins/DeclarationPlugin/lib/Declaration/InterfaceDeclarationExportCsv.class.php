<?php

interface InterfaceDeclarationExportCsv
{
    public static function getHeaderCsv();
    public function export();
    public function __construct($document, $header = true, $region = null);

}
