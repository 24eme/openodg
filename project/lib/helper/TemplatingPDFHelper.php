<?php

function styleDRev() {
    return "
    .table {
        border: 1px solid #c75268;
    }

    .tableAlt {
        border: 1px solid #f3c3d3;
    }

    .th {
        font-weight: normal; border: 1px solid #c75268; background-color: #f7dce5; color: #c75268;
    }

    .td {
        border: 1px solid #c75268; height:22px; text-align: left;
    }

    .tdAlt {
        border: 1px solid #c75268; height:22px; text-align: left; background-color: #fbedf2;
    }

    .h2 {
        text-align: left; font-size: 12pt; color: #c75268;
    }

    .tdH2 {
       border-bottom: 1px solid #c75268; 
    }
    
    .tdH2Big {
       font-weight: bold; font-size: 24pt;
    }

    .h3 {
        background-color: #c75268; color: white; font-weight: bold;
    }

    .h3Alt {
        background-color: #f3c3d3; color: #c75268; font-weight: bold;   
    }
";
}

function styleDRevMarc() {
    return "
    .table {
        border: 1px solid #1b4f8f;
    }

    .tableAlt {
        border: 1px solid #99CCFF;
    }

    .th {
        font-weight: normal; border: 1px solid #1b4f8f; background-color: #D1F0FF; color: #1b4f8f;
    }

    .td {
        border: 1px solid #1b4f8f; height:22px; text-align: left;
    }

    .tdAlt {
        border: 1px solid #1b4f8f; height:22px; text-align: left; background-color: #DAECFF;
    }

    .h2 {
        text-align: left; font-size: 12pt; color: #1b4f8f;
    }

    .tdH2 {
       border-bottom: 1px solid #1b4f8f; 
    }

    .h3 {
        background-color: #1b4f8f; color: white; font-weight: bold;
    }

    .h3Alt {
        background-color: #99CCFF; color: #00398E; font-weight: bold;   
    }
";
}

function styleConstat() {
        return "
    .table {
        border: 1px solid #ff8c00;
    }

    .tableAlt {
        border: 1px solid #ffc04d;
    }

    .th {
        font-weight: normal; border: 1px solid #ff8c00; background-color: #ffdb9a; color: #ff8c00;
    }

    .td {
        border: 1px solid #ff8c00; height:22px; text-align: left;
    }

    .tdAlt {
        border: 1px solid #ff8c00; height:22px; text-align: left; background-color: #fbedf2;
    }

    .h2 {
        text-align: left; font-size: 12pt; color: #ff8c00;
    }

    .tdH2 {
       border-bottom: 1px solid #ff8c00; 
    }
    
    .tdH2Big {
       font-weight: bold; font-size: 24pt;
    }

    .h3 {
        background-color: #ff8c00; color: white; font-weight: bold;
    }

    .h3Alt {
        background-color: #ffc04d; color: #ff8c00; font-weight: bold;   
    }
";
}


function styleParcellaire() {
    return "
    .table {
        border: 1px solid #1A8A3C;
    }

    .tableAlt {
        border: 1px solid #88DC89;
    }

    .th {
        font-weight: normal; border: 1px solid #1A8A3C; background-color: #D0FAB6; color: #1A8A3C;
    }

    .td {
        border: 1px solid #1A8A3C; height:22px; text-align: left;
    }

    .tdAlt {
        border: 1px solid #1A8A3C; height:22px; text-align: left; background-color: #D0FAB6;
    }

    .h2 {
        text-align: left; font-size: 12pt; color: #1A8A3C;
    }

    .tdH2 {
       border-bottom: 1px solid #1A8A3C; 
    }

    .h3 {
        background-color: #1A8A3C; color: white; font-weight: bold;
    }

    .h3Alt {
        background-color: #88DC89; color: #1F6320; font-weight: bold;   
    }
";
}


function styleDegustation() {
    return "

    .adresse {
    float:right;
}

    .table {
        border: 1px solid #1A8A3C;
    }

    .tableAlt {
        border: 1px solid #88DC89;
    }

    .th {
        font-weight: normal; border: 1px solid #1A8A3C; background-color: #D0FAB6; color: #1A8A3C;
    }

    .td {
        border: 1px solid #1A8A3C; height:22px; text-align: left;
    }

    .tdAlt {
        border: 1px solid #1A8A3C; height:22px; text-align: left; background-color: #D0FAB6;
    }

    .h2 {
        text-align: left; font-size: 12pt; color: #1A8A3C;
    }

    .tdH2 {
       border-bottom: 1px solid #1A8A3C; 
    }

    .h3 {
        background-color: #1A8A3C; color: white; font-weight: bold;
    }

    .h3Alt {
        background-color: #88DC89; color: #1F6320; font-weight: bold;   
    }
";
}

function tdStart() {

    return "<small style=\"font-size: 2pt;\"><br /></small>";
}

function styleTirage() {
    return "

    hr {
        border: 0px;
        border-top: 1px solid #88DC89;
    }

    td.border {
        border: 1px solid black;
        margin-left: 100px;
        padding-left: 100px;
        text-align: right;
    }

    .tableAlt {
        border: 1px solid #88DC89;
    }

    .h3Alt {
        background-color: #88DC89; color: #1F6320; font-weight: bold;   
    }

";
}

function echoCheck($text, $checked) {
    if ($checked) {
        echo '<strong><span style="font-family: Dejavusans">☒&nbsp;';
    }else{
        echo '<span style="font-family: Dejavusans">☐&nbsp;';

    }
    echo $text;
    echo '</span>';
    if ($checked) {
        echo '</strong>';
    }
    echo ' ';
}

