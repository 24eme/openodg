<?php use_helper('Degustation') ?>
Bonjour,

Les services de L'Ava viendront prélever les échantillons suivants le <?php echo Date::francizeDate($prelevement->date); ?> entre <?php echo Date::francizeHeure($prelevement->heure); ?> et <?php echo Date::francizeHeure(getHeurePlus($prelevement,2)); ?> dans votre chai situé <?php echo getAdresseChai($prelevement) ?> :

<?php foreach ($prelevement->lots as $lot): ?>
<?php echo $lot->nb; ?> lot(s) de <?php $lot->libelle ?>
<?php endforeach; ?>

Cordialement,

Le service de dégustation de l'Ava