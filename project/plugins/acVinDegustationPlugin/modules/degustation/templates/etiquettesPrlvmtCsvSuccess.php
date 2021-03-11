numero_archive;numero_dossier;declarant_nom;cvi;siret;produit_libelle;millesime;details;centilisation;numero_logement_operateur;volume
<?php
foreach ($degustation->getEtiquettesFromLots(7) as $plancheLots) {
    foreach($plancheLots as $lotInfo) {
        echo $lotInfo->lot->numero_archive.";";
        echo $lotInfo->lot->numero_dossier.";";
        echo $lotInfo->lot->declarant_nom.";";
        echo $lotInfo->etablissement->cvi.";";
        echo $lotInfo->etablissement->siret.";";
        echo $lotInfo->lot->produit_libelle.";";
        echo $lotInfo->lot->millesime.";";
        echo $lotInfo->lot->details.";";
        echo $lotInfo->lot->centilisation.";";
        echo $lotInfo->lot->numero_logement_operateur.";";
        echo $lotInfo->lot->volume."\n";
    }
}
