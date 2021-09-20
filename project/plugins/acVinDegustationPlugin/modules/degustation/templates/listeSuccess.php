<div class="page-header no-border">
    <h2>Toutes les dégustations <span class="pull-right"><a href="<?php echo url_for('degustation_liste', ['campagne' => $annee-1]) ?>"><span class="glyphicon glyphicon-arrow-down small" /></a> <?php echo $annee ?> <?php if($annee < date('Y')): ?><a href="<?php echo url_for('degustation_liste', ['campagne' => $annee+1]) ?>"><span class="glyphicon glyphicon-arrow-up small" /></a><?php endif; ?></span></h2>
</div>

<?php include_partial('degustation/liste', ['degustations' => $degustations]); ?>
