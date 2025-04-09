<?php

?>

<?php

    $arr_lots_igp = array();
    $arr_lots_aop = array();
    $arr_lots_aop_mousseux = array();

    foreach ($degustation->lots as $lot) {

        $lot_certif = $lot->getConfigProduit()->getCertification()->getKey();
        $lot_genre = $lot->getConfigProduit()->getGenre()->getLibelle();

        if (count($arr_lots_igp) == 4) {
            echo include_partial('degustation/IGPficheIndividuellePdf', array('lots' => $arr_lots_igp));
            $arr_lots_igp = array();
        } else if (count($arr_lots_aop) == 4) {
            echo include_partial('degustation/AOCficheIndividuellePdf', array('lots' => $arr_lots_aop));
            $arr_lots_aop = array();
        } else if (count($arr_lots_aop_mousseux) == 4) {
            echo include_partial('degustation/AOCficheIndividuelleMousseuxPdf', array('lots' => $arr_lots_aop_mousseux));
            $arr_lots_aop_mousseux = array();
        }

        if ($lot_certif === "AOP" && $lot_genre === "Mousseux") {
            $arr_lots_aop_mousseux[] = ['date' => $lot->date_commission, 'cepage' => $lot->getCepagesLibelle()];
        } else if ($lot_certif === "AOP") {
            $arr_lots_aop[] = ['date' => $lot->date_commission, 'cepage' => $lot->getCepagesLibelle()];
        } else if ($lot_certif === "IGP") {
            $arr_lots_igp[] = ['num' => $lot->numero_anonymat, 'cepage' => $lot->getCepagesLibelle()];
        }

    }

    if (count($arr_lots_igp) != 4) {
        while (count($arr_lots_igp) < 4) {
            $arr_lots_igp[] = null;
        }
        echo include_partial('degustation/IGPficheIndividuellePdf', array('lots' => $arr_lots_igp));
    }
    if (count($arr_lots_aop) != 4) {
        while (count($arr_lots_aop) < 4) {
            $arr_lots_aop[] = null;
        }
        echo include_partial('degustation/AOCficheIndividuellePdf', array('lots' => $arr_lots_aop));
    }
    if (count($arr_lots_aop_mousseux) != 4) {
        while (count($arr_lots_aop_mousseux) < 4) {
            $arr_lots_aop_mousseux[] = null;
        }
        echo include_partial('degustation/AOCficheIndividuelleMousseuxPdf', array('lots' => $arr_lots_aop_mousseux));
    }
