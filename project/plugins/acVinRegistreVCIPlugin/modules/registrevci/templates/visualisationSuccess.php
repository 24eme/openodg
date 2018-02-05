<?php use_helper('Date'); use_helper('Float');

 include_partial('registrevci/breadcrumb', array('registre' => $registre )); ?>

<div class="page-header no-border">
    <h2>Registre VCI <?php echo $registre->campagne; ?> <small class="pull-right"></small></h2>
</div>
<div class="row">
    <div class="col-xs-12">
        <h3>Résumé du registre VCI</h3>
        <table class="table table-striped">
        <thead><tr>
            <th class="col-md-4">Produit</th>
            <th class="text-center col-md-1">Constiuté</th>
        	  <th class="text-center col-md-1">Rafraîchi</th>
            <th class="text-center col-md-1">Complément</th>
            <th class="text-center col-md-1">Substitution</th>
            <th class="text-center col-md-1">Destruction</th>
            <th class="text-center col-md-1">Stock</th>
        </tr></thead>
<?php foreach ($registre->declaration as $ph => $p) : ?>
      <tr>
          <td><?php echo $p->libelle; ?></td>
          <td class="text-right"><?php echo echoFloat($p->constitue); ?></td>
          <td class="text-right"><?php echo echoFloat($p->rafraichi); ?></td>
          <td class="text-right"><?php echo echoFloat($p->complement); ?></td>
          <td class="text-right"><?php echo echoFloat($p->substitution); ?></td>
          <td class="text-right"><?php echo echoFloat($p->destruction); ?></td>
          <td class="text-right"><?php echo echoFloat($p->stock_final); ?></td>
      </tr>
<?php endforeach; ?>
</table>
</div></div>
<div class="row">
    <div class="col-xs-12">
        <h3>Détails du registre VCI</h3>
        <table class="table table-striped">
        <thead><tr>
            <th class="col-md-3">Produit</th>
            <th class="col-md-1">Date</th>
        	  <th class="col-md-2">Lieu</th>
            <th class="col-md-1">Type de mvmt</th>
            <th class="col-md-1">Volume</th>
            <th class="col-md-1">Stock résultant</th>
        </tr></thead>
<?php foreach ($registre->mouvements as $i => $d): ?>
      <tr>
          <td><?php echo $d->produit_libelle; ?></td>
          <td><?php echo $d->date; ?></td>
          <td><?php echo $d->detail_libelle; ?></td>
          <td><?php echo RegistreVCIClient::MOUVEMENT_LIBELLE($d->mouvement_type); ?></td>
          <td class="text-right"><?php echo echoFloat($d->volume); ?></td>
          <td class="text-right"><?php echo echoFloat($d->stock_resultant); ?></td>
      </tr>
<?php endforeach; ?>
      </table>
    </div>
</div>
