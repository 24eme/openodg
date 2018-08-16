<?php printf("\xef\xbb\xbf"); //UTF8 BOM (pour windows) ?>
Identifiant;CVI Opérateur;Siret Opérateur;Nom Opérateur;Adresse Opérateur;Code postal Opérateur;Commune Opérateur;Email;Demande;Statut;Date demande;Date habilitation;libellé activités;produit;Id du doc
<?php
    $declarants = array();
    foreach ($docs as $doc){
        if(!array_key_exists($doc->id,$declarants)){
            $hab = HabilitationClient::getInstance()->find($doc->id);
            $declarants[$doc->id] = $hab->getDeclarant();
        }
    }
?>
<?php foreach ($docs as $doc):
    $declarant = $declarants[$doc->id];
    ?>
    <?php echo $doc->key[HabilitationDemandeView::KEY_IDENTIFIANT] ?>;<?php echo $declarant->cvi ?>;<?php echo $declarant->siret ?>;"<?php echo sfOutputEscaper::unescape($declarant->raison_sociale); ?>";"<?php echo sfOutputEscaper::unescape($declarant->adresse); ?>";<?php echo $declarant->code_postal ?>;<?php echo $declarant->commune ?>;<?php echo str_replace(";",",",$declarant->email) ?>;<?php echo $doc->key[HabilitationDemandeView::KEY_DEMANDE] ?>;<?php echo $doc->key[HabilitationDemandeView::KEY_STATUT] ?>;<?php echo $doc->key[HabilitationDemandeView::KEY_DATE] ?>;<?php echo $doc->key[HabilitationDemandeView::KEY_DATE_HABILITATION] ?>;<?php echo $doc->key[HabilitationDemandeView::KEY_LIBELLE] ?>;<?php echo $doc->key[HabilitationDemandeView::KEY_PRODUIT] ?>;<?php echo $doc->id ?><?php echo "\n" ?>
<?php endforeach; ?>
