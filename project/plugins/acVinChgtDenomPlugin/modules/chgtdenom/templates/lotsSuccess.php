<?php use_helper('Float') ?>
<?php include_partial('chgtdenom/breadcrumb', array('chgtDenom' => $chgtDenom )); ?>
<?php include_partial('chgtdenom/step', array('step' => 'lots', 'chgtDenom' => $chgtDenom)) ?>
<div class="page-header">
    <h2>Changement de dénomination / Déclassement</h2>
    <div class="row">
      <table class="table table-condensed table-striped">
        <thead>
            <th class="col-sm-1 text-right">Lot</th>
            <th class="col-sm-1 text-center">Date</th>
            <th class="col-sm-4">Appellation</th>
            <th class="col-sm-1 text-right">Volume</th>
            <th class="col-sm-1 text-center">Etat</th>
            <th class="col-sm-1"></th>
        </thead>
        <tbody>
        <?php foreach($lots as $k => $lot): ?>
        <tr>
            <td class="text-right"><strong><?php echo $lot->numero; ?></strong></td>
            <td class="text-center"><strong><?php echo $lot->date; ?></strong></td>
            <td><strong><?php echo $lot->produit_libelle; ?></strong>&nbsp;<small class="text-muted"><?php echo $lot->details; ?></small></td>
            <td class="text-right"><strong><?php echo echoFloat($lot->volume); ?>&nbsp;hl</strong></td>
            <td class="text-muted text-center"><?php echo ($lot->preleve) ? 'Prélevé' : '' ; ?></td>
            <td><a href="<?php echo url_for("chgtdenom_edition", array("sf_subject" => $chgtDenom, 'key' => $k)) ?>" class="btn btn-default">Modifier</a></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
</div>
