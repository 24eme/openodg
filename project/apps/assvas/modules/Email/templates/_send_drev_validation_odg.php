Bonjour,

La Déclaration de Revendication <?= $drev->campagne ?> de <?= $drev->declarant->raison_sociale ?> vient d'être finalisée sur la plateforme.

Vous pouvez aller la consulter via le lien suivant :
<?php echo sfContext::getInstance()->getRouting()->generate('drev_visualisation', $drev, true); ?>

<?php echo include_partial('Email/footerMail'); ?>
