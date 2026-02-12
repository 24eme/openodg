<?php use_helper("Date"); ?>

<ol class="breadcrumb">
  <li><a href="<?php echo url_for('accueil'); ?>">Contrôles</a></li>
  <li class="active"><a href="<?php echo url_for('accueil'); ?>">Organisation des contrôles</a></li>
</ol>


<h2>Contrôles terrain à gérer</h2>

<table class="table table-bordered table-striped">
    <thead>
    <tr>
        <th class="col-xs-2">Date de la tournée</th>
        <th class="col-xs-2">Type du controle</th>
        <th class="col-xs-1 text-center">Nb opérateurs</th>
        <th class="col-xs-1 text-center">Nb parcelles</th>
        <th class="col-xs-5"></th>
    </tr>
    </thead>
    <tbody>
<?php foreach ($tournees as $tournee): ?>
    <tr>
        <td><?php echo Date::francizeDate($tournee['date_tournee']); ?></td>
        <td><?php echo $tournee['type_tournee']; ?></td>
        <td class="text-center"><?php echo count($tournee['operateurs']); ?></td>
        <td class="text-center"><?php echo count($tournee['parcelles']); ?></td>
        <td class="text-right">
            <a href="<?php echo url_for('controle_apporga', array('date' => $tournee['date_tournee'])); ?>" class="btn btn-sm <?php if($tournee['statut'] == ControleClient::CONTROLE_STATUT_A_ORGANISER): ?>btn-primary<?php else: ?>btn-default<?php endif; ?>"><span class="glyphicon glyphicon-th-list"></span> Préparer la tournée</a>
            <a href="<?php echo url_for('controle_appterrain', array('date' => $tournee['date_tournee'])); ?>" class="btn btn-sm <?php if($tournee['statut'] == ControleClient::CONTROLE_STATUT_PLANIFIE): ?>btn-primary<?php else: ?>btn-default<?php endif; ?>"><span class="glyphicon glyphicon-road"></span> Voir la tournée</a>
            <a href="<?php echo url_for('controle_liste_operateur_tournee', array('date' => $tournee['date_tournee'])); ?>" class="btn btn-sm <?php if($tournee['statut'] == ControleClient::CONTROLE_STATUT_EN_MANQUEMENT): ?>btn-primary<?php else: ?>btn-default<?php endif; ?>"><span class="glyphicon glyphicon-cog"></span> Gérer les manquements</a>
        </td>
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
        <td class="text-right"><a href="<?php echo url_for("controle_liste_manquements_controle", array('id' => $controle->_id)) ?>" class="btn btn-sm btn-default"><span class="glyphicon glyphicon-cog"></span> Gérer les manquements</a> <a href="<?php echo url_for('controle_set_date_tournee', $controle); ?>" class="btn btn-sm btn-primary"><span class="glyphicon glyphicon-time"></span> Planifier le controle</a></td>
    </tr>
<?php endforeach; ?>
    </tbody>
</table>
