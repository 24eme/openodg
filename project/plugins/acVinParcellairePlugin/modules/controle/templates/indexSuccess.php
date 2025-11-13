<h2>Controle terrrain à venir</h2>

<table class="table table-bordered table-striped">
    <thead>
    <tr>
        <th class="col-4">Date du controle</th>
        <th class="col-2">Nb opérateurs</th>
        <th class="col-2">Nb parcelles</th>
        <th class="col-1"></th>
    </tr>
    </thead>
    <tbody>
<?php foreach ($stats as $date_tournee => $stat): ?>
    <tr>
        <td><?php echo $date_tournee; ?></td>
        <td><?php echo count($stat['operateurs']); ?></td>
        <td><?php echo $stat['nb_parcelles']; ?></td>
        <td><a href="<?php echo url_for('controle_appterrain', array('date' => $date_tournee)); ?>" class="btn btn-sm btn-default">Accéder à la tournée</a></td>
    </tr>
<?php endforeach; ?>
    </tbody>
</table>



<h2>Opérateur dont le controle doit être planifié</h2>

<table class="table table-bordered table-striped">
    <thead>
    <tr>
        <th class="col-4">Opérateur</th>
        <th class="col-2">Commune / Secteur</th>
        <th class="col-2">Cave coopérative</th>
        <th class="col-1">Nombre de parcelles</th>
        <th class="col-1"></th>
    </tr>
    </thead>
    <tbody>
<?php foreach ($controles[ControleClient::CONTROLE_STATUT_A_PLANIFIER] as $controle): ?>
    <tr>
        <td><?php echo $controle->declarant->nom; ?> <span class="text-muted"><?php echo $controle->identifiant; ?> - <?php echo $controle->declarant->cvi; ?></span></td>
        <td><span class="text-muted"><?php echo $controle->declarant->commune; ?> - </span> <?php echo $controle->secteur; ?></td>
        <td><?php echo $controle->getLibelleLiaison(); ?></td>
        <td><?php echo count($controle->parcelles); ?></td>
        <td><a href="<?php echo url_for('controle_set_date_tournee', $controle); ?>" class="btn btn-sm btn-default">Affecter une date</a></td>
    </tr>
<?php endforeach; ?>
    </tbody>
</table>


<h2>Opérateur dont le controle est à organiser</h2>

<table class="table table-bordered table-striped">
    <thead>
    <tr>
        <th class="col-4">Opérateur</th>
        <th class="col-2">Commune / Secteur</th>
        <th class="col-3">Cave coopérative</th>
        <th class="col-1"></th>
    </tr>
    </thead>
    <tbody>
<?php foreach ($controles[ControleClient::CONTROLE_STATUT_A_ORGANISER] as $controle): ?>
    <tr>
        <td><?php echo $controle->declarant->nom; ?> <span class="text-muted"><?php echo $controle->identifiant; ?> - <?php echo $controle->declarant->cvi; ?></span></td>
        <td><span class="text-muted"><?php echo $controle->declarant->commune; ?></span> <?php echo $controle->secteur; ?></td>
        <td><?php echo str_replace(', ', '<br/>', $controle->getLibelleLiaison()); ?></td>
        <td><a href="<?php echo url_for('controle_parcelles', $controle); ?>" class="btn btn-sm btn-default">Affecter des parcelles</a></td>
    </tr>
<?php endforeach; ?>
    </tbody>
</table>

<h2>Manquements à gérer</h2>
