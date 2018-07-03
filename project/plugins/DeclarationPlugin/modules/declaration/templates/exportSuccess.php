<?php printf("\xef\xbb\xbf"); //UTF8 BOM (pour windows) ?>
Type de déclaration;Campagne;Identifiant (CVI);Raison sociale;Commune;Email;Date de validation;Mode de déclaration;Statut;Informations complementaires;Id du document
<?php foreach ($docs as $doc): ?>
<?php echo $doc->key[DeclarationTousView::KEY_TYPE] ?>;<?php echo $doc->key[DeclarationTousView::KEY_CAMPAGNE] ?>;<?php echo $doc->key[DeclarationTousView::KEY_IDENTIFIANT] ?>;"<?php echo sfOutputEscaper::unescape($doc->key[DeclarationTousView::KEY_RAISON_SOCIALE]); ?>";"<?php echo $doc->key[DeclarationTousView::KEY_COMMUNE] ?>";"<?php echo str_replace(";",",",$doc->key[DeclarationTousView::KEY_EMAIL]); ?>";<?php echo $doc->key[DeclarationTousView::KEY_DATE] ?>;<?php echo $doc->key[DeclarationTousView::KEY_MODE] ?>;<?php echo $doc->key[DeclarationTousView::KEY_STATUT] ?>;<?php echo $doc->key[DeclarationTousView::KEY_INFOS] ?>;<?php echo $doc->id ?>

<?php endforeach; ?>
