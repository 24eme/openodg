<?php

const FICHE_INDIV_IGP = 'degustation/IGPficheIndividuellePdf';


function send_to_partial($arr_lots, $certif)
{
    while (count($arr_lots[$certif]) < 4) {
        $arr_lots[$certif][] = null;
    }
    echo include_partial($certif, array('lots' => $arr_lots[$certif]));
}



    $arr_lots = [];
    $arr_lots[FICHE_INDIV_IGP] = [];


    $old_type = $lots[0]->getConfigProduit()->getCertification()->getKey() . '-' . $lots[0]->getConfigProduit()->getGenre()->getLibelle();

    foreach ($lots as $lot) {
        $new_certif = $lot->getConfigProduit()->getCertification()->getKey();
        $new_genre = $lot->getConfigProduit()->getGenre()->getLibelle();

        if (($old_type != $new_certif . '-' . $new_genre) || $is_full) {
            $old_certif = explode('-', $old_type)[0];
            $old_genre = explode('-', $old_type)[1];

            $old_type = $new_certif . '-' . $new_genre;

            if ($old_certif === "IGP") {
                send_to_partial($arr_lots, FICHE_INDIV_IGP);
                $arr_lots[FICHE_INDIV_IGP] = [];
                $is_full = false;
            }
        }

//ici on repartit les lots dans le sous tableau correspondant et on note si c'est full
        if ($new_certif === "IGP") {
            $arr_lots[FICHE_INDIV_IGP][] = $lot;
            if (count($arr_lots[FICHE_INDIV_IGP]) == 4) {
                $is_full = true;
            }
        }
    }

    foreach ($arr_lots as $certif => $lot) {
        if (count($arr_lots[$certif]) != 0) {
            send_to_partial($arr_lots, $certif);
            $arr_lots[$certif] = [];
        }
    }
