<div class="row" style="margin-top: 20px;">
    <div class="col-xs-12">
         <div class="list-group">
        <?php foreach($tournees as $tournee): ?>
            <?php $d = $tournee->getRawValue(); ?>
            <?php $nb_operateurs = count((array) $d->operateurs); ?>
            <?php $nb_degustateurs = 0; foreach($d->degustateurs as $degustateurs_type): $nb_degustateurs += count((array) $degustateurs_type); endforeach; ?>
            <?php $nb_tournees = 0; foreach($d->agents as $agent): $nb_tournees += count((array) $agent->dates); endforeach; ?>
            <?php $nb_degustations = 0; foreach($d->operateurs as $operateur): $nb_degustations += count((array) $operateur->prelevements); endforeach; ?>
            <a href="<?php if(!$tournee->validation): ?><?php echo url_for('degustation_edit', $tournee) ?><?php else: ?><?php echo url_for('degustation_visualisation', $tournee) ?><?php endif; ?>" class="list-group-item col-xs-12">
                <span class="col-xs-3 text-muted">
                <?php echo ucfirst(format_date($d->date, "P", "fr_FR")) ?>
                </span>
                <span class="col-xs-2 text-muted">
                <?php echo $d->appellation; ?>
                </span>
                <span class="col-xs-5 text-muted">

                <?php if($tournee->date > date('Y-m-d')): ?>
                    <?php echo $nb_operateurs ?> opérateurs, <?php echo $nb_degustateurs ?> dégustateurs et <?php echo count((array) $d->agents); ?> tournées
                <?php else: ?>
                    <?php //echo $nb_degustations ?>28 vins dégustés
                <?php endif; ?>
                </span>
                <span class="col-xs-2 text-muted text-right">
                    <?php if(!$tournee->validation): ?>
                    <span class="label label-default">Saisie</span>
                    <?php elseif($tournee->date < date('Y-m-d')): ?>
                    <span class="label label-danger">Terminé</span>
                    <?php else: ?>
                    <span class="label label-info">Tournée</span>
                    <?php endif; ?>
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
            <a href="<?php //echo url_for('degustation_degustation') ?>" class="list-group-item col-xs-12">
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