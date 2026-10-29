Bonjour,

Une facture est disponible, vous pouvez la télécharger directement en cliquant sur ce lien : <?php echo ProjectConfiguration::getAppRouting()->generate('piece_public_view', array('doc_id' => $id, 'auth' => UrlSecurity::generateAuthKey($id)), true) ?>


Bien cordialement,

<?php echo include_partial('Email/footerMail', array('email' => Organisme::getInstance()->getEmailFacturation())); ?>
