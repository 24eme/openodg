<?php use_helper('Date') ?>
Bonjour,

En votre qualité de dégustateur expert <?php echo str_replace(" ".$degustation->millesime, "", $degustation->getRawValue()->libelle) ?>, nous vous invitons à venir participer à une dégustation conseil :

Le <?php echo ucfirst(format_date($degustation->date, "P", "fr_FR")) ?> à <?php echo Date::francizeHeure($degustation->heure); ?>


À la Maison des Vins d’Alsace, 12 avenue de la Foire Aux Vins à Colmar.

Nous vous rappelons l’importance de votre présence pour la bonne tenue des commissions de dégustation.

Merci de bien vouloir nous confirmer votre présence ou votre absence par retour de mail.


Afin d'optimiser vos déplacements, les autres dégustateurs conviés à cette dégustation sont :
<?php foreach($degustation->degustateurs as $degustateursType): ?><?php foreach($degustateursType as $degustateur): ?>
- <?php echo $degustateur->nom ?> (<?php echo $degustateur->commune ?>)
<?php endforeach; ?><?php endforeach; ?>

Bien cordialement,

<?php echo include_partial('Email/footerMail'); ?>
