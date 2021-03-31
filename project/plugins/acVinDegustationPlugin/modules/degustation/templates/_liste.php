<?php use_helper('Date'); ?>

<?php if (count($degustations)): ?>
<table class="table table-condensed table-striped table-bordered">
<thead>
    <th class="col-sm-2">Date de dégustation</th>
    <th class="col-sm-1">Numéro</th>
    <th class="col-sm-6">Lieu de la dégustation</th>
    <th class="col-sm-1 text-center">Infos</th>
    <th class="col-sm-1 text-center"></th>
</thead>
<tbody>
<?php foreach($degustations as $d): ?>
    <tr>
        <td><?php echo format_date($d->date, 'dd/MM/yyyy HH:mm:ss', 'fr_FR'); ?></td>
        <td><span class="text-muted"><?php echo substr($d->campagne, 0, 4) ?></span>&nbsp;<?php echo $d->numero_archive; ?></td>
        <td><?php echo $d->lieu; ?></td>
        <td class="text-right">
            <?php echo ($d->lots) ? count($d->lots) : '0'; ?> <span class="text-muted">lots</span>
        </td>
        <td class="text-right">
            <a href="<?php echo url_for('degustation_visualisation', $d)?>"class="btn btn-default"><?= ($d->etape)? DegustationEtapes::$libelles_short[$d->etape] : 'Lots'  ?></a>
        </td>
    </tr>
<?php endforeach; ?>
<tbody>
</table>
<?php endif; ?>

