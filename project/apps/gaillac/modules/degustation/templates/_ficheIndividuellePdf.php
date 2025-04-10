<?php

    $arr_lots = [];
    $arr_lots['degustation/IGPficheIndividuellePdf'] = [];
    $arr_lots['degustation/AOCficheIndividuellePdf'] = [];
    $arr_lots['degustation/AOCficheIndividuelleMousseuxPdf'] = [];
//    $arr_lots['degustation/AOCficheIndividuelleBaseMousseuxPdf'] = [];

    foreach ($degustation->lots as $lot) {

        $lot_certif = $lot->getConfigProduit()->getCertification()->getKey();
        $lot_genre = $lot->getConfigProduit()->getGenre()->getLibelle();

        foreach ($arr_lots as $certif => $lots) {
            if (count($arr_lots[$certif]) == 4) {
                echo include_partial($certif, array('lots' => $arr_lots[$certif]));
            }
        }

        if ($lot_certif === "AOP" && $lot_genre === "Mousseux") {
            $arr_lots['degustation/AOCficheIndividuelleMousseuxPdf'][] = $lot;
        } else if ($lot_certif === "AOP") {
            $arr_lots['degustation/AOCficheIndividuellePdf'][] = $lot;
        } else if ($lot_certif === "IGP") {
            $arr_lots['degustation/IGPficheIndividuellePdf'][] = $lot;
        }

    }

    foreach ($arr_lots as $certif => $lots) {
        if (count($arr_lots[$certif]) != 4) {
            while (count($arr_lots[$certif]) < 4) {
                $arr_lots[$certif][] = null;
            }
            echo include_partial($certif, array('lots' => $arr_lots[$certif]));
        }
    }
