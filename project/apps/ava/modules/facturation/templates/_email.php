Bonjour,

Une facture est disponible sur notre portail de dématérialisation, vous pouvez la télécharger directement en cliquant sur ce lien : <?php echo ProjectConfiguration::getAppRouting()->generate('piece_public_view', array('doc_id' => $id, 'auth' => UrlSecurity::generateAuthKey($id)), true) ?>


La note explicative des cotisations est également disponible pour téléchargement : https://declaration.ava-aoc.fr/docs/explications_cotisations.pdf

Enfin, l'ensemble de l'historique de vos factures et règlements est disponible dans votre espace de facturation : <?php echo ProjectConfiguration::getAppRouting()->generate('facturation_declarant', array("identifiant" => explode("-", $id)[1]), true); ?>


Bien cordialement,

L'Association des Viticulteurs d'Alsace
