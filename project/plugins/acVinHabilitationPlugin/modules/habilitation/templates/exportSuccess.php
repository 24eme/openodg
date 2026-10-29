<?php printf("\xef\xbb\xbf"); //UTF8 BOM (pour windows) ?>
Identifiant;CVI Opérateur;Siret Opérateur;Nom Opérateur;Adresse Opérateur;Code postal Opérateur;Commune Opérateur;Email;Produit;Activité;Statut;Date;Commentaire;Id du doc
<?php
    $declarants = array();
foreach ($docs as $doc):
    if(!array_key_exists($doc->id,$declarants)){
        $hab = HabilitationClient::getInstance()->find($doc->id);
        $declarants[$doc->id] = $hab->getDeclarant();
    }
?>
<?php if(!array_key_exists($doc->id,$declarants)): ?>
<?php echo $doc->key[HabilitationActiviteView::KEY_IDENTIFIANT] ?>;<?php echo $doc->key[HabilitationActiviteView::KEY_CVI] ?>;<?php echo $doc->key[HabilitationActiviteView::KEY_SIRET] ?>;"<?php echo sfOutputEscaper::unescape($doc->key[HabilitationActiviteView::KEY_RAISON_SOCIALE]); ?>";"<?php echo sfOutputEscaper::unescape($doc->key[HabilitationActiviteView::KEY_ADRESSE]); ?>";<?php echo $doc->key[HabilitationActiviteView::KEY_CODE_POSTAL] ?>;<?php echo $doc->key[HabilitationActiviteView::KEY_COMMUNE] ?>;<?php echo str_replace(";",",",$doc->key[HabilitationActiviteView::KEY_EMAIL]) ?>;"<?php echo $doc->key[HabilitationActiviteView::KEY_PRODUIT_LIBELLE] ?>";<?php echo $doc->key[HabilitationActiviteView::KEY_ACTIVITE] ?>;<?php echo $doc->key[HabilitationActiviteView::KEY_STATUT] ?>;<?php echo $doc->key[HabilitationActiviteView::KEY_DATE] ?>;<?php echo $doc->key[HabilitationActiviteView::KEY_COMMENTAIRE] ?>;<?php echo $doc->id ?><?php echo "\n"; ?>
<?php else:
    $declarant = $declarants[$doc->id];
    ?>
<?php echo $doc->key[HabilitationActiviteView::KEY_IDENTIFIANT] ?>;<?php echo $declarant->cvi ?>;<?php echo $declarant->siret ?>;"<?php echo sfOutputEscaper::unescape($declarant->raison_sociale); ?>";"<?php echo sfOutputEscaper::unescape($declarant->adresse); ?>";<?php echo $declarant->code_postal ?>;<?php echo $declarant->commune ?>;<?php echo str_replace(";",",",$declarant->email) ?>;"<?php echo sfOutputEscaper::unescape($doc->key[HabilitationActiviteView::KEY_PRODUIT_LIBELLE]) ?>";<?php echo $doc->key[HabilitationActiviteView::KEY_ACTIVITE] ?>;<?php echo $doc->key[HabilitationActiviteView::KEY_STATUT] ?>;<?php echo $doc->key[HabilitationActiviteView::KEY_DATE] ?>;<?php echo $doc->key[HabilitationActiviteView::KEY_COMMENTAIRE] ?>;<?php echo $doc->id ?><?php echo "\n"; ?>
<?php endif; ?>
<?php endforeach; ?>
