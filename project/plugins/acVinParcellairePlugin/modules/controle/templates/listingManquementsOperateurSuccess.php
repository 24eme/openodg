<?php use_helper('Date'); ?>

<div class="well mb-5">
    <?php include_partial('etablissement/blocDeclaration', ['etablissement' => $controle->getEtablissementObject()]); ?>
</div>

<h3>Manquements du <?php echo $controle->_id?> </h3>

<table class="table table-bordered table-striped">
    <thead>
    <tr>
        <th class="col-1">Point de contrôle</th>
        <th class="col-3">Manquement</th>
        <th class="col-4">Parcelles concernées - Observations</th>
        <th class="col-1">Délais</th>
        <th class="col-1"></th>
    </tr>
    </thead>
    <tbody>
<?php foreach ($sorted_manquements as $numRtm => $manquement): ?>
    <tr>
        <td><?php echo $manquement->libelle_point_de_controle ?></td>
        <td><?php echo $manquement->libelle_manquement ?></td>
        <td><?php echo $manquement->observations ?></td>
        <td><?php echo $manquement->delais ?></td>
        <td class="text-center">
            <?php if (!$manquement->cloture_date): ?>
                <a href="<?php echo url_for('controle_lever_manquement', ['id_controle' => $controle->_id, 'id_manquement' => $numRtm]); ?>" class="btn btn-sm btn-primary">Lever le manquements</a>
            <?php else: ?>
                <a class="btn btn-sm btn-primary" disabled>Levé le <?php echo format_date($manquement->cloture_date, "dd/MM/yyyy", "fr_FR"); ?></a>
            <?php endif; ?>
        </td>
    </tr>
<?php endforeach; ?>
        </tbody>
    </table>
