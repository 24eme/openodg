Bonjour,

Une nouvelle facture est disponible, vous pouvez la télécharger directement en cliquant sur ce lien : <?php echo ProjectConfiguration::getAppRouting()->generate('piece_public_view', array('doc_id' => $id, 'piece_id' => '0', 'auth' => UrlSecurity::generateAuthKey($id.'0')), true) ?>


Bien cordialement,

<?php echo include_partial('Email/footerMail'); ?>