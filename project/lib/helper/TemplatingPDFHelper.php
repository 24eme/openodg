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


function tdStart() {

    return "<small style=\"font-size: 2pt;\"><br /></small>";
}

?>