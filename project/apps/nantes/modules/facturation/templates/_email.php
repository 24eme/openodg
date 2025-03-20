Bonjour,

Une nouvelle facture est disponible, vous pouvez la télécharger directement en cliquant sur ce lien : <?php echo ProjectConfiguration::getAppRouting()->generate('piece_public_view', array('doc_id' => $id, 'auth' => UrlSecurity::generateAuthKey($id)), true) ?>

Si vous souhaitez opter pour le prélèvement automatique vous pouvez compléter et nous renvoyer ce mandat de prélèvement SEPA : https://declaration.vinsdenantes.com/docs/Demande_prelevement_Mandat_SEPA.pdf

Vous pouvez également consulter le courrier d'information des cotisations de la récolte 2024 : https://declaration.vinsdenantes.com/docs/Cotisations_FVN_recolte_2024.pdf

Bien cordialement,

<?php echo include_partial('Email/footerMail', array('email' => Organisme::getInstance()->getEmailFacturation())); ?>
