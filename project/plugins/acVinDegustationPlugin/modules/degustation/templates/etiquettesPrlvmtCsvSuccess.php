numero_archive;numero_dossier;declarant_nom;cvi;siret;code_postal;ville;produit_libelle;millesime;details;centilisation;numero_logement_operateur;volume;labo
<?php


foreach ($degustation->getEtiquettesFromLots(7) as $plancheLots) {
    foreach($plancheLots as $lotInfo) {
        $lotInfo = $lotInfo->getRawValue();
        echo str_replace(';', ' ', $lotInfo->lot->numero_archive).";";
        echo str_replace(';', ' ', $lotInfo->lot->numero_dossier).";";
        echo iconv("UTF-8", "ISO-8859-1", str_replace(';', ' ', $lotInfo->lot->declarant_nom)).";";
        echo str_replace(';', ' ', $lotInfo->etablissement->cvi).";";
        echo str_replace(';', ' ', $lotInfo->etablissement->siret).";";
        echo str_replace(';', ' ', $lotInfo->etablissement->code_postal).";";
        echo str_replace(';', ' ', $lotInfo->etablissement->commune).";";
        echo iconv("UTF-8", "ISO-8859-1", str_replace(';', ' ', $lotInfo->lot->produit_libelle)).";";
        echo str_replace(';', ' ', $lotInfo->lot->millesime).";";
        echo iconv("UTF-8", "ISO-8859-1", str_replace(';', ' ', $lotInfo->lot->details)).";";
        echo str_replace(';', ' ', $lotInfo->lot->centilisation).";";
        echo iconv("UTF-8", "ISO-8859-1", str_replace(';', ' ', $lotInfo->lot->numero_logement_operateur)).";";
        echo str_replace(';', ' ', $lotInfo->lot->volume)."\n";
        echo str_replace(';', ' ', $lotInfo->etablissement->getLaboLibelle())."\n";
    }
}
