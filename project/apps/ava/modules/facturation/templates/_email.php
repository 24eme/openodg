Bonjour,

L'AVA a développé un espace "Facture" sur son portail de dématérialisation afin de vous permettre de télécharger chacune d'entre elles.

Vous trouverez ainsi sur cet espace la facture suivante : <?php echo ProjectConfiguration::getAppRouting()->generate('piece_public_view', array('doc_id' => $id, 'auth' => UrlSecurity::generateAuthKey($id)), true) ?>


La note explicative des cotisations est également disponible pour téléchargement : https://declaration.ava-aoc.fr/docs/explications_cotisations.pdf

Enfin, l'ensemble de l'historique de vos factures et règlements sont également disponible dans votre espace de facturation : <?php echo ProjectConfiguration::getAppRouting()->generate('facturation_declarant', array("identifiant" => explode("-", $id)[1]), true); ?>


Bien cordialement,

L'Association des Viticulteurs d'Alsace
