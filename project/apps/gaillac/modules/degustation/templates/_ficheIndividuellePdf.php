<?php use_helper('TemplatingPDF'); ?>
<?php use_helper('Lot') ?>
<style>
<?php echo style(); ?>
</style>


<?php

    $limite = 0;
    foreach ($degustation->lots as $lot) {
        if ($limite == 4) {
            $limite = 0;
            echo include_partial('degustation/IGPficheIndividuellePdf', array('lots' => $arr_lots));
            $arr_lots = array();
        }
        $arr_lots[] = [$lot->numero_anonymat, $lot->cepages ? "AOC ". $limite : "null"];
        $limite += 1;
    }
    if ($limite != 0) {
        while ($limite < 4) {
            $arr_lots[] = ["empty", ""];
            $limite++;
        }
        echo include_partial('degustation/IGPficheIndividuellePdf', array('lots' => $arr_lots));
    }

// echo include_partial('degustation/IGPficheIndividuellePdf', array('lots' => $arr_lots));

// echo include_partial('degustation/AOCficheIndividuellePdf', array('lots' => $arr_lots));
