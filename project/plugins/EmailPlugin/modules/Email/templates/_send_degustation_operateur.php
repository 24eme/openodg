<?php use_helper('Degustation') ?>
Bonjour,

Les services de L'Ava viendront prélever les échantillons suivants le <?php echo Date::francizeDate($operateur->date); ?> entre <?php echo Date::francizeHeure($operateur->heure); ?> et <?php echo Date::francizeHeure(getHeurePlus($operateur, 2)); ?> dans votre chai situé <?php echo getAdresseChai($operateur) ?> :

<?php foreach ($operateur->lots as $lot): ?>
<?php echo $lot->nb; ?> lot(s) de <?php $lot->libelle ?>
<?php endforeach; ?>

Cordialement,

Le service de dégustation de l'Ava