<?php

?>

<?php

    $arr_lots['degustation/IGPficheIndividuellePdf'] = array();
    $arr_lots['degustation/AOCficheIndividuellePdf'] = array();
    $arr_lots['degustation/AOCficheIndividuelleMousseuxPdf'] = array();

    foreach ($degustation->lots as $lot) {

        $lot_certif = $lot->getConfigProduit()->getCertification()->getKey();
        $lot_genre = $lot->getConfigProduit()->getGenre()->getLibelle();

        if (count($arr_lots['degustation/IGPficheIndividuellePdf']) == 4) {


            echo include_partial('degustation/IGPficheIndividuellePdf', array('lots' => $arr_lots['degustation/IGPficheIndividuellePdf']));
            $arr_lots['degustation/IGPficheIndividuellePdf'] = array();
        } else if (count($arr_lots['degustation/AOCficheIndividuellePdf']) == 4) {


            echo include_partial('degustation/AOCficheIndividuellePdf', array('lots' => $arr_lots['degustation/AOCficheIndividuellePdf']));
            $arr_lots['degustation/AOCficheIndividuellePdf'] = array();
        } else if (count($arr_lots['degustation/AOCficheIndividuelleMousseuxPdf']) == 4) {


            echo include_partial('degustation/AOCficheIndividuelleMousseuxPdf', array('lots' => $arr_lots['degustation/AOCficheIndividuelleMousseuxPdf']));
            $arr_lots['degustation/AOCficheIndividuelleMousseuxPdf'] = array();
        }

        if ($lot_certif === "AOP" && $lot_genre === "Mousseux") {
            $arr_lots['degustation/AOCficheIndividuelleMousseuxPdf'][] = $lot;
        } else if ($lot_certif === "AOP") {
            $arr_lots['degustation/AOCficheIndividuellePdf'][] = $lot;
        } else if ($lot_certif === "IGP") {
            $arr_lots['degustation/IGPficheIndividuellePdf'][] = $lot;
        }

    }

    if (count($arr_lots['degustation/IGPficheIndividuellePdf']) != 4) {
        while (count($arr_lots['degustation/IGPficheIndividuellePdf']) < 4) {
            $arr_lots['degustation/IGPficheIndividuellePdf'][] = null;
        }
        echo include_partial('degustation/IGPficheIndividuellePdf', array('lots' => $arr_lots['degustation/IGPficheIndividuellePdf']));
    }
    if (count($arr_lots['degustation/AOCficheIndividuellePdf']) != 4) {
        while (count($arr_lots['degustation/AOCficheIndividuellePdf']) < 4) {
            $arr_lots['degustation/AOCficheIndividuellePdf'][] = null;
        }
        echo include_partial('degustation/AOCficheIndividuellePdf', array('lots' => $arr_lots['degustation/AOCficheIndividuellePdf']));
    }
    if (count($arr_lots['degustation/AOCficheIndividuelleMousseuxPdf']) != 4) {
        while (count($arr_lots['degustation/AOCficheIndividuelleMousseuxPdf']) < 4) {
            $arr_lots['degustation/AOCficheIndividuelleMousseuxPdf'][] = null;
        }
        echo include_partial('degustation/AOCficheIndividuelleMousseuxPdf', array('lots' => $arr_lots['degustation/AOCficheIndividuelleMousseuxPdf']));
    }
