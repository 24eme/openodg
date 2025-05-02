<?php

    $arr_lots = [];
    $arr_lots['degustation/IGPficheIndividuellePdf'] = [];
    $arr_lots['degustation/AOCficheIndividuellePdf'] = [];
    $arr_lots['degustation/AOCficheIndividuelleMousseuxPdf'] = [];
//    $arr_lots['degustation/AOCficheIndividuelleBaseMousseuxPdf'] = [];

    $old_type = $lots[0]->getConfigProduit()->getCertification()->getKey() . '-' . $lots[0]->getConfigProduit()->getGenre()->getLibelle();

    foreach ($lots as $lot) {
        $new_certif = $lot->getConfigProduit()->getCertification()->getKey();
        $new_genre = $lot->getConfigProduit()->getGenre()->getLibelle();

        if ($old_type != $new_certif . '-' . $new_genre) {
            $old_certif = explode('-', $old_type)[0];
            $old_genre = explode('-', $old_type)[1];

            $old_type = $new_certif . '-' . $new_genre;

            if ($old_certif === "AOP" && $old_genre === "Mousseux") {
                $certif = 'degustation/AOCficheIndividuelleMousseuxPdf';
                while (count($arr_lots[$certif]) < 4) {
                    $arr_lots[$certif][] = null;
                }
                echo include_partial($certif, array('lots' => $arr_lots[$certif]));
                $arr_lots[$certif] = [];

            } else if ($old_certif === "AOP") {
                print_r("ping");exit;
                $certif = 'degustation/AOCficheIndividuellePdf';
                while (count($arr_lots[$certif]) < 4) {
                    $arr_lots[$certif][] = null;
                }
                echo include_partial($certif, array('lots' => $arr_lots[$certif]));
                $arr_lots[$certif] = [];

            } else if ($old_certif === "IGP") {
                $certif = 'degustation/IGPficheIndividuellePdf';
                while (count($arr_lots[$certif]) < 4) {
                    $arr_lots[$certif][] = null;
                }
                echo include_partial($certif, array('lots' => $arr_lots[$certif]));
                $arr_lots[$certif] = [];
            }
        }


        if ($new_certif === "AOP" && $new_genre === "Mousseux") {
            $arr_lots['degustation/AOCficheIndividuelleMousseuxPdf'][] = $lot;
        } else if ($new_certif === "AOP") {
            $arr_lots['degustation/AOCficheIndividuellePdf'][] = $lot;
        } else if ($new_certif === "IGP") {
            $arr_lots['degustation/IGPficheIndividuellePdf'][] = $lot;
        }
    }

    foreach ($arr_lots as $certif => $lot) {
        if (count($arr_lots[$certif]) != 0) {
            while (count($arr_lots[$certif]) < 4) {
                $arr_lots[$certif][] = null;
            }
            echo include_partial($certif, array('lots' => $arr_lots[$certif]));
            $arr_lots[$certif] = [];
        }
    }
