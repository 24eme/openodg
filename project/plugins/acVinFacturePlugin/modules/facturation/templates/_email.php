Bonjour,

Une nouvelle facture est disponible, vous pouvez la télécharger directement en cliquant sur le lien : <?php echo ProjectConfiguration::getAppRouting()->generate('facturation_pdf_auth', array('id' => $id, 'auth' => UrlSecurity::generateAuthKey($id)), true) ?>>

Bien cordialement,

<?php echo include_partial('Email/footerMail'); ?>