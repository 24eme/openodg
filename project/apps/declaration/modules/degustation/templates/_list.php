<div class="row" style="margin-top: 20px;">
    <div class="col-xs-12">
         <div class="list-group">
        <?php foreach($degustations as $degustation): ?>
            <?php $d = $degustation->getRawValue(); ?>
             <a href="<?php echo url_for('degustation_affectation', $degustation) ?>" class="list-group-item col-xs-12">
                <span class="col-xs-2 text-muted">
                <?php echo $d->date; ?>
                </span>
                <span class="col-xs-2 text-muted">
                <?php echo $d->appellation; ?>
                </span>
                <span class="col-xs-6 text-muted">
                <?php echo count((array) $d->operateurs); ?> opérateurs, <?php echo count((array) $d->degustateurs); ?> dégustateurs et <?php echo count((array) $d->agents); ?> tournées
                </span>
                <span class="col-xs-2 text-muted text-right">
                    <span class="label label-default">Saisie</span>
                </span>
            </a>
        <?php endforeach; ?>
            <!--<a href="" class="list-group-item col-xs-12">
                <span class="col-xs-2 text-muted">
                <?php echo $d->date; ?>
                </span>
                <span class="col-xs-2 text-muted">
                <?php echo $d->appellation; ?>
                </span>
                <span class="col-xs-6 text-muted">
                50 opérateurs, 12 dégustateurs et 5 tournées
                </span>
                <span class="col-xs-2 text-muted text-right">
                    <span class="label label-info">Dégustation</span>
                </span>
            </a>
            <a href="<?php //echo url_for('degustation_prelevements') ?>" class="list-group-item col-xs-12">
                <span class="col-xs-2 text-muted">
                20/02/2014
                </span>
                <span class="col-xs-2 text-muted">
                AOC Alsace
                </span>
                <span class="col-xs-6 text-muted">
                50 opérateurs, 12 dégustateurs et 5 tournées
                </span>
                <span class="col-xs-2 text-muted text-right">
                    <span class="label label-default">Saisie</span>
                </span>
            </a>
            <a href="<?php //echo url_for('degustation_affectation') ?>" class="list-group-item col-xs-12">
                <span class="col-xs-2 text-muted">
                20/02/2014
                </span>
                <span class="col-xs-2 text-muted">
                AOC Alsace
                </span>
                <span class="col-xs-6 text-muted">
                50 opérateurs, 12 dégustateurs et 5 tournées
                </span>
                <span class="col-xs-2 text-muted text-right">
                    <span class="label label-warning">Affectation des vins</span>
                </span>
            </a>
            <a href="<?php echo url_for('degustation_degustation') ?>" class="list-group-item col-xs-12">
                <span class="col-xs-2 text-muted">
                20/02/2014
                </span>
                <span class="col-xs-2 text-muted">
                AOC Alsace
                </span>
                <span class="col-xs-6 text-muted">
                50 opérateurs, 12 dégustateurs et 5 tournées
                </span>
                <span class="col-xs-2 text-muted text-right">
                    <span class="label label-danger">Dégustation</span>
                </span>
            </a>
            <a href="" class="list-group-item col-xs-12">
                <span class="col-xs-2 text-muted">
                20/02/2014
                </span>
                <span class="col-xs-2 text-muted">
                AOC Alsace
                </span>
                <span class="col-xs-6 text-muted">
                50 opérateurs, 12 dégustateurs et 5 tournées
                </span>
                <span class="col-xs-2 text-muted text-right">
                    <span class="label label-success">Terminé</span>
                </span>
            </a>-->
        </div>
</div>