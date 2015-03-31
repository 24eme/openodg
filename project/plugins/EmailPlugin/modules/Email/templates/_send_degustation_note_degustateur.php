<?php use_helper('Date') ?>
Bonjour,

Vos vins ont été dégustés par les services de L'AVA à la dégustation conseil du <?php echo ucfirst(format_date($degustation->date, "P", "fr_FR")) ?> à <?php echo Date::francizeHeure($degustation->heure); ?>

Voici en pièce jointe les différentes remarque qui ont été rapportés lors de cette Dégustation.

Bien cordialement,

Le service Appui technique de l'AVA