<?php printf("\xef\xbb\xbf"); //UTF8 BOM (pour windows) ?>
Identifiant;CVI Opérateur;Siret Opérateur;Nom Opérateur;Adresse Opérateur;Adresse complémentaire 1;Adresse complémentaire 2;Code postal Opérateur;Commune Opérateur;Email;Demande;Statut;Date demande;Date habilitation;Date complétude;Statut suivant;libellé activités;produit;Id du doc
<?php
    $declarants = array();
    $habilitions = array();
    foreach ($rows as $row){
        if(!array_key_exists($row->id,$declarants)){
            $hab = HabilitationClient::getInstance()->find($row->id);
            $habilitions[$row->id] = $hab;
            $declarants[$row->id] = $hab->getDeclarant();
        }
    }
?>
<?php foreach ($rows as $row):
    if($row->key[HabilitationDemandesExportView::KEY_STATUT] != "ENREGISTREMENT"){ continue; }

    $hab = $habilitions[$row->id];
    $demande = $hab->demandes->get($row->key[HabilitationDemandesExportView::KEY_DEMANDE_KEY]);
    $lastCompletude = $demande->getlastCompletudeDemande();
    $nextStatut = $demande->getNextStatut();

    $dateLastCompletude = ($lastCompletude)? $lastCompletude->date : "" ;

    $declarant = $declarants[$row->id];
    $adresse = str_replace('"', '', $declarant->adresse);
    $acs = explode('−',$declarant->adresse_complementaire);
    $adresse_complementaire = "";
    $adresse_complementaire_bis = "";
    $adresse_complementaire = str_replace('"', '', $acs[0]);
    if(count($acs) > 1){
        $adresse_complementaire_bis = str_replace('"', '', $acs[1]);
    }
    ?>
    <?php echo $row->key[HabilitationDemandesExportView::KEY_IDENTIFIANT] ?>;<?php echo $declarant->cvi ?>;<?php echo $declarant->siret ?>;"<?php echo sfOutputEscaper::unescape($declarant->raison_sociale); ?>";"<?php echo sfOutputEscaper::unescape($adresse); ?>";"<?php echo sfOutputEscaper::unescape($adresse_complementaire); ?>";"<?php echo sfOutputEscaper::unescape($adresse_complementaire_bis); ?>";<?php echo $declarant->code_postal ?>;<?php echo $declarant->commune ?>;<?php echo str_replace(";",",",$declarant->email) ?>;<?php echo $row->key[HabilitationDemandesExportView::KEY_DEMANDE] ?>;<?php echo $row->key[HabilitationDemandesExportView::KEY_STATUT] ?>;<?php echo $row->key[HabilitationDemandesExportView::KEY_DATE] ?>;<?php echo $row->key[HabilitationDemandesExportView::KEY_DATE_HABILITATION] ?>;<?php echo $dateLastCompletude ?>;<?php echo $nextStatut ?>;<?php echo $row->key[HabilitationDemandesExportView::KEY_LIBELLE] ?>;<?php echo $row->key[HabilitationDemandesExportView::KEY_PRODUIT] ?>;<?php echo $row->id ?><?php echo "\n" ?>
<?php endforeach; ?>
