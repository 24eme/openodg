<?php use_javascript('hamza_style.js'); ?>
<?php use_helper('Date'); ?>

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
        <th class="col-xs-1">Date création</th>
        <th class="col-xs-7">Opérateur</th>
        <th class="col-xs-2">Type de controle</th>
        <th class="col-xs-2"></th>
    </tr>
    </thead>
    <tbody>
<?php foreach ($controles[ControleClient::CONTROLE_STATUT_A_PLANIFIER] as $controle): ?>
    <?php
        $hamza = array($controle->declarant->nom, $controle->identifiant, $controle->declarant->cvi, $controle->declarant->commune, $controle->secteur, $controle->type_tournee);
        $caves = $controle->getLibelleLiaison();
        if ($caves) {
            $caves = explode(', ', $caves);
        } else {
            $caves = array();
            $hamza[] = 'non coopérateur';
        }
        $hamza = array_merge($hamza, $caves);
    ?>
    <tr class="hamzastyle-item" data-words='<?php echo json_encode($hamza, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE); ?>'>
        <td><?php echo format_date($controle->date); ?></td>
        <td>
            <a href="<?php echo url_for('controle_operateur', ['identifiant' => $controle->identifiant]); ?>"><?php echo $controle->declarant->nom; ?></a>
             <span class="text-muted"> -
                 <?php echo $controle->declarant->commune; ?> -
                <?php echo $controle->identifiant; ?> -
                <?php echo $controle->declarant->cvi; ?>
            </span>
             <br/>
            <?php $has_secteur = false; ?>
            <?php if ($controle->secteur): ?>
                Sect. : <?php echo $controle->secteur; ?>
            <?php $has_secteur = true; endif; ?>
            <?php if (count($caves)): ?>
                <?php if ($has_secteur) : ?> - <?php endif; ?>
                Caves : <abbr title="<?php echo implode("\n", $caves); ?>"><?php echo count($caves); ?></abbr>
            <?php endif; ?>
        </td>
        <td><?php echo $controle->type_tournee; ?></td>
        <td class="text-right">
            <a href="<?php echo url_for('controle_set_date_tournee', $controle); ?>" class="btn btn-sm btn-primary"><span class="glyphicon glyphicon-time"></span> Planifier le controle</a>
        </td>
    </tr>
<?php endforeach; ?>
    </tbody>
</table>
<div class="row col-xs-12">
    <a href="<?php echo url_for('controle_index') ?>" class="btn btn-default"><span class="glyphicon glyphicon-chevron-left"></span> Retour</a>
</div>
