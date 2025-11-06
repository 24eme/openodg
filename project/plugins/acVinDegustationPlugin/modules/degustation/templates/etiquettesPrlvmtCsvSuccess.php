numero_dossier;numero_lot;declarant_nom;cvi;siret;"numero interne";code_postal;ville;produit_libelle;millesime;details;centilisation;numero_logement_operateur;volume;"numero_anonymat";logement;adresse opérateur;téléphone opérateur;email;labo;
<?php


foreach ($degustation->getEtiquettesFromLots(7) as $plancheLots) {
    foreach($plancheLots as $lotInfo) {
        $lotInfo = $lotInfo->getRawValue();
        echo str_replace(';', ' ', $lotInfo->lot->numero_dossier).";";
        echo str_replace(';', ' ', $lotInfo->lot->numero_archive).";";
        echo iconv("UTF-8", "ISO-8859-1", str_replace(';', ' ', $lotInfo->lot->declarant_nom)).";";
        echo str_replace(';', ' ', $lotInfo->etablissement->cvi).";";
        echo str_replace(';', ' ', $lotInfo->etablissement->siret).";";
        echo str_replace(';', ' ', $lotInfo->etablissement->num_interne).";";
        echo str_replace(';', ' ', $lotInfo->etablissement->code_postal).";";
        echo str_replace(';', ' ', $lotInfo->etablissement->commune).";";
        echo iconv("UTF-8", "ISO-8859-1", str_replace(';', ' ', $lotInfo->lot->produit_libelle).' '.str_replace(';', ' ', $lotInfo->lot->getCepagesLibelle(false))).";";
        echo str_replace(';', ' ', $lotInfo->lot->millesime).";";
        echo iconv("UTF-8", "ISO-8859-1", str_replace(';', ' ', $lotInfo->lot->details)).";";
        echo str_replace(';', ' ', $lotInfo->lot->centilisation).";";
        echo iconv("UTF-8", "ISO-8859-1", str_replace(';', ' ', $lotInfo->lot->numero_logement_operateur)).";";
        echo str_replace(';', ' ', $lotInfo->lot->volume).";";
        echo str_replace(';', ' ', $lotInfo->lot->numero_anonymat).";";
        echo str_replace(';', ' ', $lotInfo->adresse_logement).";";
        echo str_replace(';', ' ', $lotInfo->etablissement->adresse)." ".str_replace(';', ' ', $lotInfo->etablissement->code_postal)." ".str_replace(';', ' ', $lotInfo->etablissement->commune).";";
        echo ($lotInfo->etablissement->telephone_mobile)  ? str_replace(';', ' ', $lotInfo->etablissement->telephone_mobile) : str_replace(';', ' ', $lotInfo->etablissement->telephone_bureau); echo ";";
        echo str_replace(';', ' ', $lotInfo->etablissement->email).";";
        echo str_replace(';', ' ', $lotInfo->etablissement->getLaboLibelle()).";";
        echo "\n";
    }
}
