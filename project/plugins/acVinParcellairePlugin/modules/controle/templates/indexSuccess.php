<?php use_helper("Date"); ?>

<ol class="breadcrumb">
  <li><a href="<?php echo url_for('accueil'); ?>">Contrôles</a></li>
  <li class="active"><a href="<?php echo url_for('accueil'); ?>">Organisation des contrôles</a></li>
</ol>


<h2>Contrôles terrain à venir</h2>

<table class="table table-bordered table-striped">
    <thead>
    <tr>
        <th class="col-4">Date de la tournée</th>
        <th class="col-2">Type du controle</th>
        <th class="col-2 text-center">Nb opérateurs</th>
        <th class="col-2 text-center">Nb parcelles</th>
        <th class="col-1"></th>
    </tr>
    </thead>
    <tbody>
<?php foreach ($stats[ControleClient::CONTROLE_STATUT_PLANIFIE] as $stat): ?>
    <tr>
        <td><?php echo Date::francizeDate($stat['date_tournee']); ?></td>
        <td><?php echo $stat['type_tournee']; ?></td>
        <td class="text-center"><?php echo count($stat['operateurs']); ?></td>
        <td class="text-center"><?php echo count($stat['parcelles']); ?></td>
        <td>
            <a href="<?php echo url_for('controle_appterrain', array('date' => $stat['date_tournee'])); ?>" class="btn btn-sm btn-primary">Accéder à la tournée</a>
            <a href="<?php echo url_for('controle_liste_operateur_tournee', array('date' => $stat['date_tournee'])); ?>" class="btn btn-sm btn-default">Visualiser les manquements</a>
            <a href="<?php echo url_for('controle_apporga', array('date' => $stat['date_tournee'])); ?>" class="btn btn-sm btn-secondary">Modifier l'organisation</a>
        </td>
    </tr>
<?php endforeach; ?>
    </tbody>
</table>



<h2>Contrôles à organiser</h2>

<table class="table table-bordered table-striped">
    <thead>
    <tr>
        <th class="col-4">Date de la tournée</th>
        <th class="col-2">Type du controle</th>
        <th class="col-2 text-center">Nb opérateurs</th>
        <th class="col-1"></th>
    </tr>
    </thead>
    <tbody>
<?php foreach ($stats[ControleClient::CONTROLE_STATUT_A_ORGANISER] as $stat): ?>
    <tr>
        <td><?php echo Date::francizeDate($stat['date_tournee']); ?></td>
        <td><?php echo $stat['type_tournee']; ?></td>
        <td class="text-center"><?php echo count($stat['operateurs']); ?></td>
        <td><a href="<?php echo url_for('controle_apporga', array('date' => $stat['date_tournee'])); ?>" class="btn btn-sm btn-primary">Organiser la tournée</a></td>
    </tr>
<?php endforeach; ?>
    </tbody>
</table>


<h2>Opérateur dont le contrôle est à planifier</h2>

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
