<ol class="breadcrumb">
  <li class="active"><a href="<?php echo url_for('degustation'); ?>">Dégustation</a></li>
  <li><a href=""><?php echo $etablissement->getNom() ?> (<?php echo $etablissement->identifiant ?> - <?php echo $etablissement->cvi ?>)</a></li>
</ol>
<?php use_helper('Float') ?>

<div class="page-header no-border">
    <h2>Les lots de <?php echo $etablissement->getNom(); ?></h2>
</div>
<?php if (count($lots)): ?>
<div class="row">
<table class="table table-condensed table-striped">
<thead>
    <th class="col-sm-2 text-center">Date</th>
    <th class="col-sm-3">Appellation</th>
    <th class="col-sm-1">Volume</th>
    <th class="col-sm-2 text-center">Étape</th>
    <th class="col-sm-1"></th>
    <th class="col-sm-1"></th>
</thead>
<tbody>
<?php foreach($lots as $l): ?>
    <tr>
        <td class="text-center"><strong><?php echo $l->date; ?></strong></td>
        <td><strong><?php echo $l->produit_libelle; ?></strong>&nbsp;<small class="text-muted"><?php echo $l->details; ?></small></td>
        <td class="text-right"><strong><?php echo echoFloat($l->volume); ?>&nbsp;hl</strong></td>
        <td></td>
        <td></td>
        <td></td>
    </tr>
    <?php foreach($l->steps as $s): ?>
        <tr>
            <td></td>
            <td></td>
            <td></td>
            <td class="text-center"><?php echo preg_replace('/-.*/', '', $s->id_document); ?></td>
            <td class="text-muted"><?php echo ($s->preleve) ? 'Prélevé' : '' ; ?></td>
            <td><a href="#" class="btn btn-default">Voir</a></td>
        </tr>
    <?php endforeach; ?>
<?php endforeach; ?>
<tbody>
</table>
<?php endif; ?>
</div>
