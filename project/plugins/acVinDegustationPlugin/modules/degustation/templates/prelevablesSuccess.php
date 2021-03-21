<?php use_helper('Float'); ?>
<?php use_helper('Lot'); ?>
<?php use_helper('Date') ?>

<?php include_partial('degustation/breadcrumb'); ?>

<div class="page-header no-border">
    <h2>Liste des lots à prélever : <span class=""><?= count($lotsPrelevables) ?></span></h2>
</div>
<div class="row">
<table class="table table-condensed table-striped">
<thead>
    <th class="col-sm-1">Date</th>
    <th class="col-sm-1">N° Dossier</th>
    <th class="col-sm-1">N° Archive</th>
    <th class="col-xs-2">Opérateur</th>
    <th class="col-xs-1">Provenance</th>
    <th class="col-xs-1">Logement</th>
    <th class="col-xs-4">Produit (millésime, spécificité)</th>
    <th class="col-xs-1">Volume</th>
</thead>
<tbody>
<?php foreach($lotsPrelevables as $key => $lot): ?>
  <tr>
      <td><?php echo format_date($lot->date, "dd/MM/yyyy", "fr_FR");  ?></td>
      <td><?php echo $lot->numero_dossier;  ?></td>
      <td><?php echo $lot->numero_archive;  ?></td>
    <?php include_partial('degustation/rowTablePrelevable', ['lot' => $lot]) ?>
  </tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
