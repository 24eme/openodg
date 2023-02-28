Bonjour,

Une nouvelle facture est disponible, vous pouvez la télécharger directement en cliquant sur ce lien : <?php echo ProjectConfiguration::getAppRouting()->generate('piece_public_view', array('doc_id' => $id, 'auth' => UrlSecurity::generateAuthKey($id)), true) ?>

Vous pouvez également consulter le courrier d'information des cotisations de la récolte 2022 : https://declaration.vinsdenantes.com/docs/Cotisations_FVN_recolte_2022_vd.pdf

Bien cordialement,

<?php echo include_partial('Email/footerMail', array('email' => Organisme::getInstance()->getEmailFacturation())); ?>
