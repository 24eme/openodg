<div class="page-header no-border">
    <h2>Toutes les dégustations de l'établissement <?php echo $etablissement ?> pour la campagne  <?php echo $campagne ?></h2>
</div>

<?php include_partial('degustation/liste', ['degustations' => $degustations]); ?>

