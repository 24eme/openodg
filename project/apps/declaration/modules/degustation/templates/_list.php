<div class="row row-margin">
    <div class="col-xs-12">
         <div class="list-group">
        <?php foreach($tournees as $tournee): ?>
            <?php $t = $tournee->getRawValue(); ?>
            <?php $nb_operateurs = count((array) $t->degustations); ?>
            <?php $nb_degustateurs = 0; foreach($t->degustateurs as $degustateursType): $nb_degustateurs += count((array) $degustateursType); endforeach; ?>
            <?php $nb_tournees = 0; foreach($t->agents as $agent): $nb_tournees += count((array) $agent->dates); endforeach; ?>
            <?php $nb_prelevements = 0; ?>
            <?php if($tournee->statut == TourneeClient::STATUT_SAISIE): ?>
                <a href="<?php echo url_for('degustation_edit', $tournee) ?>" class="list-group-item col-xs-12">
                    <span class="col-xs-3 text-muted"><?php echo ucfirst(format_date($tournee->date, "P", "fr_FR")) ?></span>
                    <span class="col-xs-2 text-muted"><?php echo (isset($tournee->libelle)) ? $tournee->libelle : $tournee->appellation_libelle; ?></span>
                    <span class="col-xs-5 text-muted"></span>
                    <span class="col-xs-2 text-muted text-right">
                        <span class="label label-default">Saisie</span>
                    </span>
                </a>
            <?php elseif($tournee->statut == TourneeClient::STATUT_ORGANISATION): ?>
                <a href="<?php echo url_for('degustation_edit', $tournee) ?>" class="list-group-item col-xs-12">
                    <span class="col-xs-3 text-muted"><?php echo ucfirst(format_date($tournee->date, "P", "fr_FR")) ?></span>
                    <span class="col-xs-2 text-muted"><?php echo (isset($tournee->libelle)) ? $tournee->libelle : $tournee->appellation_libelle; ?></span>
                    <span class="col-xs-5 text-muted"><?php echo $nb_operateurs ?> opérateurs, <?php echo $nb_degustateurs ?> dégustateurs et <?php echo $nb_tournees ?> tournées</span>
                    <span class="col-xs-2 text-muted text-right">
                        <span class="label label-default">Organisation</span>
                    </span>
                </a>
            <?php elseif($tournee->statut == TourneeClient::STATUT_TOURNEES): ?>
                <a href="<?php echo url_for('degustation_visualisation', $tournee) ?>" class="list-group-item col-xs-12">
                    <span class="col-xs-3 text-muted"><?php echo ucfirst(format_date($tournee->date, "P", "fr_FR")) ?></span>
                    <span class="col-xs-2 text-muted"><?php echo (isset($tournee->libelle)) ? $tournee->libelle : $tournee->appellation_libelle; ?></span>
                    <span class="col-xs-5 text-muted"><?php echo $nb_operateurs ?> opérateurs, <?php echo $nb_degustateurs ?> dégustateurs et <?php echo $nb_tournees; ?> tournées</span>
                    <span class="col-xs-2 text-muted text-right">
                        <span class="label label-info">Tournées</span>
                    </span>
                </a>
            <?php elseif($tournee->statut == TourneeClient::STATUT_AFFECTATION): ?>
                <a href="<?php echo url_for('degustation_visualisation', $tournee) ?>" class="list-group-item col-xs-12">
                    <span class="col-xs-3 text-muted"><?php echo ucfirst(format_date($tournee->date, "P", "fr_FR")) ?></span>
                    <span class="col-xs-2 text-muted"><?php echo (isset($tournee->libelle)) ? $tournee->libelle : $tournee->appellation_libelle; ?></span>
                    <span class="col-xs-5 text-muted"><?php echo $tournee->nombre_prelevements ?> prélevements</span>
                    <span class="col-xs-2 text-muted text-right">
                        <span class="label label-warning">Affectation</span>
                    </span>
                </a>
            <?php elseif($tournee->statut == TourneeClient::STATUT_DEGUSTATIONS): ?>
                <a href="<?php echo url_for('degustation_visualisation', $tournee) ?>" class="list-group-item col-xs-12">
                    <span class="col-xs-3 text-muted"><?php echo ucfirst(format_date($tournee->date, "P", "fr_FR")) ?></span>
                    <span class="col-xs-2 text-muted"><?php echo (isset($tournee->libelle)) ? $tournee->libelle : $tournee->appellation_libelle; ?></span>
                    <span class="col-xs-5 text-muted"><?php echo $tournee->nombre_prelevements ?> vins à déguster</span>
                    <span class="col-xs-2 text-muted text-right">
                        <span class="label label-danger">Dégustations</span>
                    </span>
                </a>
            <?php elseif($tournee->statut == TourneeClient::STATUT_COURRIERS): ?>
                <a href="<?php echo url_for('degustation_visualisation', $tournee) ?>" class="list-group-item col-xs-12">
                    <span class="col-xs-3 text-muted"><?php echo ucfirst(format_date($tournee->date, "P", "fr_FR")) ?></span>
                    <span class="col-xs-2 text-muted"><?php echo (isset($tournee->libelle)) ? $tournee->libelle : $tournee->appellation_libelle; ?></span>
                    <span class="col-xs-5 text-muted"><?php echo $tournee->nombre_prelevements ?> vins dégustés</span>
                    <span class="col-xs-2 text-muted text-right">
                        <span class="label label-success"><span class="glyphicon glyphicon-envelope"></span>&nbsp;&nbsp;Courriers à envoyer</span>
                    </span>
                </a>
            <?php elseif($tournee->statut == TourneeClient::STATUT_TERMINE): ?>
                <a href="<?php echo url_for('degustation_visualisation', $tournee) ?>" class="list-group-item col-xs-12">
                    <span class="col-xs-3 text-muted"><?php echo ucfirst(format_date($tournee->date, "P", "fr_FR")) ?></span>
                    <span class="col-xs-2 text-muted"><?php echo (isset($tournee->libelle)) ? $tournee->libelle : $tournee->appellation_libelle; ?></span>
                    <span class="col-xs-5 text-muted"><?php echo $tournee->nombre_prelevements ?> vins dégustés</span>
                    <span class="col-xs-2 text-muted text-right">
                        <span class="label label-success">Terminé</span>
                    </span>
                </a>
            <?php else: ?>
                <a href="<?php echo url_for('degustation_edit', $tournee) ?>" class="list-group-item col-xs-12">
                    <span class="col-xs-3 text-muted"><?php echo ucfirst(format_date($tournee->date, "P", "fr_FR")) ?></span>
                    <span class="col-xs-2 text-muted"><?php echo (isset($tournee->libelle)) ? $tournee->libelle : $tournee->appellation_libelle; ?></span>
                    <span class="col-xs-5 text-muted"><?php echo $nb_operateurs ?> opérateurs, <?php echo $nb_degustateurs ?> dégustateurs et <?php echo $nb_tournees ?> tournées</span>
            <?php endif; ?>
        <?php endforeach; ?>
        </div>
</div>
