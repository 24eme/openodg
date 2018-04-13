Bonjour,

Votre facture <?php echo $facture->getTemplate()->libelle ?> pour l'Association des Viticulteurs d'Alsace est disponible, vous pouvez la télécharger à ce lien : <?php echo url_for('facturation_pdf', array("id" => "COMPTE-".$facture->identifiant), true); ?>

Vous trouverez également la note explicative de vos cotisations :

Une nouvelle facture est disponible dans votre espace déclaratif sur le portail de l'Association des Viticulteurs d'Alsace : <?php echo url_for('facturation_declarant', array("id" => "COMPTE-".$facture->identifiant), true); ?>

Bien cordialement,

L'Association des Viticulteurs d'Alsace
