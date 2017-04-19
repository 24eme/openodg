<h3>Liste des tournées de dégustation</h3>
<table class="table table-bordered table-striped table-condensed">
    <thead>
        <tr>
            <th class="col-xs-3">Date</th>
            <th class="col-xs-4">Appellation</th>
            <th class="col-xs-3">Infos</th>
            <th class="col-xs-2">Statut</th>
        </tr>
    </thead>
    <?php foreach($tournees as $tournee): ?>
        <?php $t = $tournee->getRawValue(); ?>
        <?php $nb_operateurs = count((array) $t->degustations); ?>
        <?php $nb_degustateurs = 0; foreach($t->degustateurs as $degustateursType): $nb_degustateurs += count((array) $degustateursType); endforeach; ?>
        <?php $nb_tournees = 0; foreach($t->agents as $agent): $nb_tournees += count((array) $agent->dates); endforeach; ?>
        <?php $nb_prelevements = 0; ?>
    <tr>
        <td><?php echo ucfirst(format_date($tournee->date, "P", "fr_FR")) ?></td>
        <td><?php echo (isset($tournee->libelle)) ? $tournee->libelle : $tournee->appellation_libelle; ?></td>
        <td><?php echo $nb_operateurs ?> opérateurs <small>(<?php echo $nb_tournees; ?> tournées)</small><br />
            <?php echo $tournee->nombre_prelevements ?> prélèvements
        </td>
        <td class="text-center"><a href="<?php if (in_array($tournee->statut, array(TourneeClient::STATUT_SAISIE, TourneeClient::STATUT_ORGANISATION))): ?><?php echo url_for('degustation_edit', $tournee) ?><?php else: ?><?php echo url_for('degustation_visualisation', $tournee) ?><?php endif; ?>" class="btn btn-block btn-sm btn-<?php echo TourneeClient::$couleursStatut[$tournee->statut] ?>"><?php echo TourneeClient::$statutsLibelle[$tournee->statut] ?></a></td>
    </tr>
    <?php endforeach; ?>
</table>
