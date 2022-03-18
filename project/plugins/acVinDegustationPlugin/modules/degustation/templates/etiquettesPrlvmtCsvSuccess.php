numero_archive;numero_dossier;declarant_nom;cvi;siret;code_postal;ville;produit_libelle;millesime;details;centilisation;numero_logement_operateur;volume;labo
<?php


foreach ($degustation->getEtiquettesFromLots(7) as $plancheLots) {
    foreach($plancheLots as $lotInfo) {
        $lotInfo = $lotInfo->getRawValue();
        echo ($lotInfo->lot->numero_archive) ? str_replace(';', ' ', $lotInfo->lot->numero_archive).";" : ";";
        echo ($lotInfo->lot->numero_dossier) ? str_replace(';', ' ', $lotInfo->lot->numero_dossier).";" : ";";
        echo ($lotInfo->lot->declarant_nom) ? iconv("UTF-8", "ISO-8859-1", str_replace(';', ' ', $lotInfo->lot->declarant_nom)).";" : ";";
        echo ($lotInfo->etablissement->cvi) ? str_replace(';', ' ', $lotInfo->etablissement->cvi).";" : ";";
        echo ($lotInfo->etablissement->siret) ? str_replace(';', ' ', $lotInfo->etablissement->siret).";" : ";";
        echo ($lotInfo->etablissement->code_postal) ? str_replace(';', ' ', $lotInfo->etablissement->code_postal).";" : ";";
        echo ($lotInfo->etablissement->commune) ? str_replace(';', ' ', $lotInfo->etablissement->commune).";" : ";";
        echo iconv("UTF-8", "ISO-8859-1", str_replace(';', ' ', ($lotInfo->lot->produit_libelle) ? $lotInfo->lot->produit_libelle : '').' '.str_replace(';', ' ', ($lotInfo->lot->getCepagesLibelle(false)) ? $lotInfo->lot->getCepagesLibelle(false) : '')).";";
        echo ($lotInfo->lot->millesime) ? str_replace(';', ' ', $lotInfo->lot->millesime).";" : ";";
        echo ($lotInfo->lot->details) ? iconv("UTF-8", "ISO-8859-1", str_replace(';', ' ', $lotInfo->lot->details)).";" : ";";
        echo ($lotInfo->lot && $lotInfo->lot->centilisation) ? str_replace(';', ' ', $lotInfo->lot->centilisation).";" : ";";
        echo ($lotInfo->lot->numero_logement_operateur) ? iconv("UTF-8", "ISO-8859-1", str_replace(';', ' ', $lotInfo->lot->numero_logement_operateur)).";" : ";";
        echo ($lotInfo->lot->volume) ? str_replace(';', ' ', $lotInfo->lot->volume).";" : ";";
        echo ($lotInfo->etablissement->getLaboLibelle()) ? str_replace(';', ' ', $lotInfo->etablissement->getLaboLibelle())."\n" : "\n";
    }
}
