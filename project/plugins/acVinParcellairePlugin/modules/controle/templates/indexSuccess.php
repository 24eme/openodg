<h2>Controle terrain à venir</h2>

<table class="table table-bordered table-striped">
    <thead>
    <tr>
        <th class="col-4">Date du controle</th>
        <th class="col-2">Type du controle</th>
        <th class="col-2">Nb opérateurs / Nb parcelles</th>
        <th class="col-1"></th>
    </tr>
    </thead>
    <tbody>
<?php foreach ($stats[ControleClient::CONTROLE_STATUT_PLANIFIE] as $tournee_key => $stat): ?>
    <tr>
        <td><?php echo $stat['date_tournee']; ?></td>
        <td><?php echo $stat['type_tournee']; ?></td>
        <td><?php echo count($stat['operateurs']); ?> / <?php echo $stat['nb_parcelles']; ?></td>
        <td>
            <a href="<?php echo url_for('controle_appterrain', array('date' => $stat['date_tournee'])); ?>" class="btn btn-sm btn-primary">Accéder à la tournée</a>
            <a href="<?php echo url_for('controle_apporga', array('date' => $stat['date_tournee'])); ?>" class="btn btn-sm btn-default">Cloturer</a>
            <a href="<?php echo url_for('controle_apporga', array('date' => $stat['date_tournee'])); ?>" class="btn btn-sm btn-secondary">Modifier l'organisation</a>
        </td>
    </tr>
<?php endforeach; ?>
    </tbody>
</table>



<h2>Controle à organiser</h2>

<table class="table table-bordered table-striped">
    <thead>
    <tr>
        <th class="col-4">Date du controle</th>
        <th class="col-2">Type du controle</th>
        <th class="col-2">Nb opérateurs / Nb parcelles</th>
        <th class="col-1"></th>
    </tr>
    </thead>
    <tbody>
<?php foreach ($stats[ControleClient::CONTROLE_STATUT_A_ORGANISER] as $tournee_key => $stat): ?>
    <tr>
        <td><?php echo $stat['date_tournee']; ?></td>
        <td><?php echo $stat['type_tournee']; ?></td>
        <td><?php echo count($stat['operateurs']); ?> / <?php echo $stat['nb_parcelles']; ?></td>
        <td><a href="<?php echo url_for('controle_apporga', array('date' => $stat['date_tournee'])); ?>" class="btn btn-sm btn-primary">Organiser la tournée</a></td>
    </tr>
<?php endforeach; ?>
    </tbody>
</table>


<h2>Opérateur dont le controle est à planifier</h2>

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
<?php foreach ($controles[ControleClient::CONTROLE_STATUT_A_PLANIFIER] as $controle): ?>
    <tr>
        <td><?php echo $controle->declarant->nom; ?> <span class="text-muted"><?php echo $controle->identifiant; ?> - <?php echo $controle->declarant->cvi; ?></span></td>
        <td><span class="text-muted"><?php echo $controle->declarant->commune; ?></span> <?php echo $controle->secteur; ?></td>
        <td><?php echo str_replace(', ', '<br/>', $controle->getLibelleLiaison()); ?></td>
        <td><a href="<?php echo url_for('controle_set_date_tournee', $controle); ?>" class="btn btn-sm btn-primary">Planifier le controle</a></td>
    </tr>
<?php endforeach; ?>
    </tbody>
</table>

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
