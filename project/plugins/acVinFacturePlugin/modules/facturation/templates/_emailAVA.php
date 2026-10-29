Bonjour,

L'AVA a développé un espace "Facture" sur son portail de dématérialisation afin de vous permettre de télécharger chacune d'entre elles.

Vous trouverez ainsi sur cet espace les factures suivantes :

<?php foreach($factures as $facture): ?>
- Votre facture "<?php echo $facture->getTemplate()->libelle ?>" : <?php echo ProjectConfiguration::getAppRouting()->generate('piece_public_view', array('doc_id' => $facture->_id, 'auth' => UrlSecurity::generateAuthKey($facture->_id)), true) ?>

<?php endforeach; ?>

La note explicative de vos cotisations est également disponible pour téléchargement : https://declaration.ava-aoc.fr/docs/explications_cotisations.pdf

Enfin, l'ensemble de l'historique de vos factures et règlements sont également disponible dans votre espace de facturation : <?php echo ProjectConfiguration::getAppRouting()->generate('facturation_declarant', array("id" => "COMPTE-".$facture->identifiant), true); ?>


Bien cordialement,

L'Association des Viticulteurs d'Alsace
