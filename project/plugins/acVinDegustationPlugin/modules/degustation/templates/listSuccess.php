<?php include_partial('degustation/breadcrumb'); ?>
<?php use_helper('Float') ?>

<div class="page-header no-border">
    <h2>Les lots de XXXX</h2>
</div>
<?php if (count($lots->rows)): ?>
<div class="row">
<table class="table table-condensed table-striped">
<thead>
    <th class="col-sm-2 text-center">Date</th>
    <th class="col-sm-2">Appellation</th>
    <th class="col-sm-1">Volume</th>
    <th class="col-sm-2 text-center"></th>
</thead>
<tbody>
<?php foreach($lots->rows as $l): ?>
    <tr>
        <td class="text-center"><?php echo $l->value->date; ?></td>
        <td><?php echo $l->value->produit_libelle; ?></td>
        <td class="text-right"><?php echo echoFloat($l->value->volume); ?>&nbsp;hl</td>
        <td><?php echo $l->value->prelevable; ?></td>
        <td><?php echo $l->value->preleve; ?></td>
        <td><?php echo $l->value->origine_document_id; ?></td>
    </tr>
<?php endforeach; ?>
<tbody>
</table>
<?php endif; ?>
</div>
