<?php

function handleIfFull($base_array, $certif)
{
    if (count($base_array[$certif]) == 4) {
        echo include_partial($certif, array('lots' => $base_array[$certif]));
    }
}

function fillArray($base_array, $certif)
{
    if (count($base_array[$certif]) != 4) {
        while (count($base_array[$certif]) < 4) {
            $base_array[$certif][] = null;
        }
        echo include_partial($certif, array('lots' => $base_array[$certif]));
    }
}

?>

<?php

    $arr_lots['degustation/IGPficheIndividuellePdf'] = array();
    $arr_lots['degustation/AOCficheIndividuellePdf'] = array();
    $arr_lots['degustation/AOCficheIndividuelleMousseuxPdf'] = array();

    foreach ($degustation->lots as $lot) {

        $lot_certif = $lot->getConfigProduit()->getCertification()->getKey();
        $lot_genre = $lot->getConfigProduit()->getGenre()->getLibelle();

        foreach ($arr_lots as $certif => $lots) {
            handleIfFull($arr_lots, $certif);
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
        fillArray($arr_lots, $certif);
    }
