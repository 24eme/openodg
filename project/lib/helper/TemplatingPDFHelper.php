<?php

function style() {
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

    .h3 {
        background-color: #c75268; color: white; font-weight: bold;
    }

    .h3Alt {
        background-color: #f3c3d3; color: #c75268; font-weight: bold;   
    }
";
}

function tdStart() {

    return "<small style=\"font-size: 2pt;\"><br /></small>";
}

?>