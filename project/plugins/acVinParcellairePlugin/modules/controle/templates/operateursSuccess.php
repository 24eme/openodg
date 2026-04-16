<?php use_javascript('hamza_style.js'); ?>

<ol class="breadcrumb">
  <li><a href="<?php echo url_for('controle_index'); ?>">Contrôles</a></li>
  <li class="active"><a href="<?php echo url_for('controle_operateurs'); ?>">Plannification des opérateurs</a></li>
</ol>

<h2 class="hidden-xs">Opérateurs dont le contrôle est à planifier</h2>

<div class="mb-2">
    <input type="hidden" data-placeholder="Sélectionner un opérateur" data-hamzastyle-container=".table_operateurs" class="hamzastyle" style="width: 100%;">
</div>
<table class="table table-bordered table-striped hidden-xs table_operateurs">
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
    <tr class="hamzastyle-item" data-words='<?php echo json_encode(array($controle->declarant->nom, $controle->identifiant, $controle->declarant->cvi, $controle->declarant->commune, $controle->secteur, $controle->getLibelleLiaison()), JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE); ?>'>
        <td><?php echo $controle->declarant->nom; ?> <span class="text-muted"><?php echo $controle->identifiant; ?> - <?php echo $controle->declarant->cvi; ?></span></td>
        <td><span class="text-muted"><?php echo $controle->declarant->commune; ?></span> <?php echo $controle->secteur; ?></td>
        <td><?php echo str_replace(', ', '<br/>', $controle->getLibelleLiaison()); ?></td>
        <td class="text-right"><a href="<?php echo url_for("controle_liste_manquements_controle", array('id' => $controle->_id)) ?>" class="btn btn-sm btn-default"><span class="glyphicon glyphicon-cog"></span> Voir les manquements</a> <a href="<?php echo url_for('controle_set_date_tournee', $controle); ?>" class="btn btn-sm btn-primary"><span class="glyphicon glyphicon-time"></span> Planifier le controle</a></td>
    </tr>
<?php endforeach; ?>
    </tbody>
</table>
<div class="row col-xs-12">
    <a href="<?php echo url_for('controle_index') ?>" class="btn btn-default"><span class="glyphicon glyphicon-chevron-left"></span> Retour</a>
</div>
