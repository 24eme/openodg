<?php printf("\xef\xbb\xbf"); //UTF8 BOM (pour windows) ?>
Identifiant;CVI Opérateur;Siret Opérateur;Nom Opérateur;Adresse Opérateur;Adresse complémentaire 1;Adresse complémentaire 2;Code postal Opérateur;Commune Opérateur;Email;Téléphone Bureau;Téléphone Mobile;Demande;Libellé activités;Produit;Statut;Date Statut;Statut précédent;Date précédent statut;Statut suivant;Date statut suivant;Id du doc;Clé de la demande
<?php foreach ($rows as $row):
    $keysHash = explode(":", $row->key[HabilitationHistoriqueView::KEY_IDDOC]);
    $hab = HabilitationClient::getInstance()->find($row->id);
    $demandeHash = $keysHash[1];
    $demande = $hab->get($demandeHash);

    $historiquePrecedent = $demande->getHistoriquePrecedent($row->key[HabilitationHistoriqueView::KEY_STATUT], $row->key[HabilitationHistoriqueView::KEY_DATE]);

    $historiqueSuivant = $demande->getHistoriqueSuivant($row->key[HabilitationHistoriqueView::KEY_STATUT], $row->key[HabilitationHistoriqueView::KEY_DATE]);

    $declarant = $hab->getDeclarant();
    $adresse = str_replace('"', '', $declarant->adresse);
    $acs = explode('−',$declarant->adresse_complementaire);
    $adresse_complementaire = "";
    $adresse_complementaire_bis = "";
    $adresse_complementaire = str_replace('"', '', $acs[0]);
    if(count($acs) > 1){
        $adresse_complementaire_bis = str_replace('"', '', $acs[1]);
    }
    ?>
<?php echo $row->key[HabilitationHistoriqueView::KEY_IDENTIFIANT] ?>;<?php echo $declarant->cvi ?>;<?php echo $declarant->siret ?>;"<?php echo sfOutputEscaper::unescape($declarant->raison_sociale); ?>";"<?php echo sfOutputEscaper::unescape($adresse); ?>";"<?php echo sfOutputEscaper::unescape($adresse_complementaire); ?>";"<?php echo sfOutputEscaper::unescape($adresse_complementaire_bis); ?>";<?php echo $declarant->code_postal ?>;<?php echo $declarant->commune ?>;<?php echo str_replace(";",",",$declarant->email) ?>;<?php echo str_replace(";",",",$declarant->telephone_bureau) ?>;<?php echo str_replace(";",",",$declarant->telephone_mobile) ?>;<?php echo $demande->demande ?>;<?php echo implode(", ", $demande->getActivitesLibelle()) ?>;<?php echo $demande->produit_libelle ?>;<?php echo $row->key[HabilitationHistoriqueView::KEY_STATUT] ?>;<?php echo $row->key[HabilitationHistoriqueView::KEY_DATE] ?>;<?php echo ($historiquePrecedent) ? $historiquePrecedent->statut : null ?>;<?php echo ($historiquePrecedent) ? $historiquePrecedent->date : null ?>;<?php echo ($historiqueSuivant) ? $historiqueSuivant->statut : null ?>;<?php echo ($historiqueSuivant) ? $historiqueSuivant->date : null ?>;<?php echo $row->id ?>;<?php echo $demande->getKey() ?><?php echo "\n" ?>
<?php endforeach; ?>
