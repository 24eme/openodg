Bonjour,

Les services de L'ava viendront prélever les echantillons suivants le <?php echo $prelevement->date ?> à <?php echo $prelevement->heure ?> :

<?php foreach ($prelevement->lots as $lot): ?>
<?php echo $lot->nb; ?> lot(s) de <?php $lot->libelle ?>
<?php endforeach; ?>


Cordialement,

Le service Appui technique (via l'application de télédéclaration)