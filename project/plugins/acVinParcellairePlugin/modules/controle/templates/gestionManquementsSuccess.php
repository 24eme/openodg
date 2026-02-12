<?php include_partial('global/flash'); ?>

<h2>Manquements à gérer</h2>
<table class="table table-bordered table-striped">
    <thead>
    <tr>
        <th class="col-4">Opérateur</th>
        <th class="col-2">Commune / Secteur</th>
        <th class="col-2">Cave coopérative</th>
        <th class="col-1">Nb manquements</th>
        <th class="col-1"></th>
    </tr>
    </thead>
    <tbody>
<?php foreach ($controles[ControleClient::CONTROLE_STATUT_EN_MANQUEMENT] as $controle): ?>
    <tr>
        <td><?php echo $controle->declarant->nom; ?> <span class="text-muted"><?php echo $controle->identifiant; ?> - <?php echo $controle->declarant->cvi; ?></span></td>
        <td><span class="text-muted"><?php echo $controle->declarant->commune; ?></span> <?php echo $controle->secteur; ?></td>
        <td><?php echo str_replace(', ', '<br/>', $controle->getLibelleLiaison()); ?></td>
        <td><?php echo count($controle->manquements); ?></td>
        <td><a href="<?php echo url_for('controle_set_date_tournee', $controle); ?>" class="btn btn-sm btn-primary">Gérer les manquements</a></td>
    </tr>
<?php endforeach; ?>
        </tbody>
    </table>
