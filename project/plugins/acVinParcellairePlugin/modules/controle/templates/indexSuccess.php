<?php use_helper("Date"); ?>

<ol class="breadcrumb">
  <li><a href="<?php echo url_for('controle_index'); ?>">Contrôles</a></li>
</ol>

<?php include_partial('etablissement/formChoice', array('form' => $form, 'action' => url_for('controle_etablissement_selection'))); ?>


<h2>Contrôles à planifier</h2>

<div>
    <p><?php echo $nb_operateurs_a_planifier; ?> opérateurs à planifier</p>
    <a href="<?php echo url_for('controle_aplanifier') ?>" class="btn btn-primary">Planifier les controles</a>
</div>

<h2 class="hidden-xs">Contrôles terrain à gérer</h2>

<table class="table table-bordered table-striped hidden-xs">
    <thead>
    <tr>
        <th class="col-xs-1">Date de la tournée</th>
        <th class="col-xs-1">Agent</th>
        <th class="col-xs-1">Type du controle</th>
        <th class="col-xs-1 text-center">Nb opérateurs</th>
        <th class="col-xs-1 text-center">Nb parcelles</th>
        <th class="col-xs-3">Secteurs / Opérateurs</th>
        <th class="col-xs-3"></th>
    </tr>
    </thead>
    <tbody>
<?php foreach ($tournees as $tournee): ?>
    <tr>
        <td><?php echo Date::francizeDate($tournee['date_tournee']); ?></td>
        <td><?php echo ($tournee['agent'])? $tournee['agent']->getNomAAfficher() : '' ?></td>
        <td><?php echo $tournee['type_tournee']; ?></td>
        <td class="text-center" data-toggle="tooltip" title="<?php echo implode("\n", $tournee['operateurs']->getRawValue()) ?>"><?php echo count($tournee['operateurs']); ?></td>
        <td class="text-center"><?php echo count($tournee['parcelles']); ?></td>
        <td class="col-xs-3">
            <p class="mb-0" title="<?php echo implode("\n", $tournee['secteurs']->getRawValue()); ?>" style="text-overflow: ellipsis; white-space: nowrap; overflow: hidden; width: 300px;"><?php echo implode(", ", $tournee['secteurs']->getRawValue()); ?></p>
            <?php if(count($tournee['cooperatives'])): ?>
            <p class="mb-0 text-muted" title="<?php echo implode("\n", $tournee['cooperatives']->getRawValue()); ?>" style="text-overflow: ellipsis; white-space: nowrap; overflow: hidden; width: 300px;"><?php echo implode(", ", $tournee['cooperatives']->getRawValue()); ?></p>
            <?php else: ?>
            <p class="mb-0 text-muted" title="<?php echo implode("\n", $tournee['operateurs']->getRawValue()); ?>" style="text-overflow: ellipsis; white-space: nowrap; overflow: hidden; width: 300px;"><?php echo implode(", ", $tournee['operateurs']->getRawValue()); ?></p>
            <?php endif; ?>
        </td>
        <td class="text-right">
            <a href="<?php echo url_for('controle_apporga', array('date' => $tournee['date_tournee'], 'agent_identifiant' => ($tournee['agent'])? $tournee['agent']->identifiant : '')); ?>" class="btn btn-sm <?php if($tournee['statut'] == ControleClient::CONTROLE_STATUT_A_ORGANISER): ?>btn-primary<?php else: ?>btn-default<?php endif; ?>"><span class="glyphicon glyphicon-th-list"></span> Préparer    </a>
            <a href="<?php echo url_for('controle_appterrain', array('date' => $tournee['date_tournee'], 'agent_identifiant' => ($tournee['agent'])? $tournee['agent']->identifiant : '')); ?>" class="btn btn-sm <?php if($tournee['statut'] == ControleClient::CONTROLE_STATUT_ORGANISE): ?>btn-primary<?php else: ?>btn-default<?php endif; ?>"><span class="glyphicon glyphicon-road"></span> Tournée</a>
            <a href="<?php echo url_for('controle_liste_operateur_tournee', array('date' => $tournee['date_tournee'], 'agent_identifiant' => ($tournee['agent'])? $tournee['agent']->identifiant : '')); ?>" class="btn btn-sm <?php if($tournee['statut'] == ControleClient::CONTROLE_STATUT_EN_MANQUEMENT): ?>btn-primary<?php else: ?>btn-default<?php endif; ?>"><span class="glyphicon glyphicon-cog"></span> Manquements</a>
        </td>
    </tr>
<?php endforeach; ?>
    </tbody>
</table>

<h2 class="visible-xs">Tournées à réaliser</h2>
<ul class="list-group visible-xs">
    <?php foreach ($tournees as $tournee): ?>
        <?php if($tournee['statut'] == ControleClient::CONTROLE_STATUT_ORGANISE): ?>
        <a href="<?php echo url_for('controle_appterrain', array('date' => $tournee['date_tournee'], 'agent_identifiant' => ($tournee['agent'])? $tournee['agent']->identifiant : '')); ?>" class="list-group-item" style="margin-bottom:1rem;">
            <div class="row">
                <div class="col-xs-4 text-center">
                    <strong><?php echo Date::francizeDate($tournee['date_tournee']); ?></strong><br />
                    <?php echo $tournee['type_tournee']; ?>
                </div>
                <div class="col-xs-6">
                    <?php echo ($tournee['agent'])? $tournee['agent']->getNomAAfficher() : '' ?><br />
                    <?php echo count($tournee['operateurs']); ?> op. - <?php echo count($tournee['parcelles']); ?> parcelles
                </div>
                <div class="col-xs-2 text-right text-primary">
                    <span class="glyphicon glyphicon-chevron-right h1"></span>
                </div>
            </div>
        </a>
    <?php endif; ?>
    <?php endforeach; ?>
</ul>

<h2>Manquements à gérer</h2>

<div>
    <p><?php echo $nb_operateurs_en_manquement; ?> opérateurs en manquements</p>
    <a href="<?php echo url_for('controle_gestion_manquements', array())?>" class="btn btn-primary">Liste des manquements</a>
</div>
