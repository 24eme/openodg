<?php
$societe = $compte->getSociete();
$identifiant = $societe->getIdentifiant();
$etablissement = $societe->getEtablissementPrincipal();
$interpro = strtoupper(sfConfig::get('app_teledeclaration_interpro'));
?>
Madame, Monsieur,

Votre compte a bien été créé pour l’espace professionnel du Syndicat Général des Côtes du Rhône.

Votre identifiant est : <?php echo $identifiant ?>.

Vous pouvez dès maintenant gérer toutes vos obligations déclaratives via cet espace.

Votre syndicat reste à votre disposition pour plus d'information.

Bonne journée.

Le Syndicat Général des Vignerons réunis des Côtes du Rhône
Gestiondesdonnees@syndicat-cotesdurhone.com
04.90.27.24.24

