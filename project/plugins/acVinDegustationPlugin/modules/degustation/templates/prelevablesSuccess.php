<?php use_helper('Float'); ?>
<?php use_helper('Lot'); ?>

<?php include_partial('degustation/breadcrumb'); ?>

<div class="page-header no-border">
    <h2>Liste des lots à prélever</h2>
</div>
<div class="row">
<table class="table table-condensed table-striped">
<thead>
    <th class="col-xs-3">Opérateur</th>
    <th class="col-xs-1">Provenance</th>
    <th class="col-xs-1">Logement</th>
    <th class="col-xs-5">Produit (millésime, spécificité)</th>
    <th class="col-xs-1">Volume</th>
</thead>
<tbody>
<?php foreach($lotsPrelevables as $key => $lot): ?>
  <tr>
    <?php include_partial('degustation/rowTablePrelevable', ['lot' => $lot]) ?>
  </tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
