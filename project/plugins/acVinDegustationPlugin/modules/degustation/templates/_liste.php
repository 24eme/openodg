<?php if (count($degustations)): ?>
<table class="table table-condensed table-striped">
<thead>
    <th class="col-sm-2">Date de dégustation</th>
    <th class="col-sm-7">Lieu de la dégustation</th>
    <th class="col-sm-2">Infos</th>
    <th class="col-sm-2 text-center"></th>
</thead>
<tbody>
<?php foreach($degustations as $d): ?>
    <tr>
        <td class="col-sm-2"><?php echo format_date($d->date, 'dd/MM/yyyy HH:mm:ss', 'fr_FR'); ?></td>
        <td class="col-sm-"><?php echo $d->lieu; ?></td>
        <td class="col-sm-2">
            <?php echo ($d->lots) ? count($d->lots) : '0'; ?> <span class="text-muted">lots</span>
        </td>
        <td class="col-sm-1 text-right">
            <a href="<?php echo url_for('degustation_visualisation', $d)?>"class="btn btn-default"><?= ($d->etape)? DegustationEtapes::$libelles_short[$d->etape] : 'Lots'  ?></a>
        </td>
    </tr>
<?php endforeach; ?>
<tbody>
</table>
<?php endif; ?>

