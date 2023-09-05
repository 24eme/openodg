<?php use_helper('Date'); ?>

<?php if (count($tournees)): ?>
<table class="table table-striped table-bordered">
<thead>
    <th class="col-sm-2">Date</th>
    <th style="width: 0;" class="text-center">Heure</th>
    <th class="col-sm-1 text-center">Numéro</th>
    <th class="col-sm-6"></th>
    <th class="col-sm-1 text-center">Infos</th>
    <th class="col-sm-1 text-center"></th>
</thead>
<tbody>
<?php foreach($tournees as $d): ?>
    <?php $nbLots = 0; foreach($d->lots as $lot) { if($lot->leurre) { continue; } $nbLots++; }?>
    <tr>
        <td><?php echo ucfirst(format_date($d->date, "EEEE d MMMM yyyy", "fr_FR")) ?></td>
        <td class="text-center text-muted"><?php echo format_date($d->date, 'HH') ?>h<?php echo format_date($d->date, 'mm') ?></td>
        <td class="text-center"><span class="text-muted"><?php echo substr($d->campagne, 0, 4) ?> - </span><?php echo sprintf("%03d", $d->numero_archive); ?></td>
        <td><?php echo preg_replace("/[ ]*—.+/", "", $d->lieu); ?> <small class="text-muted"> - <?php echo preg_replace("/.+—[ ]*/", "", $d->lieu); ?></small></td>
        <td class="text-right">
            <?php echo $nbLots ?> <span class="text-muted">lots</span>
        </td>
        <td class="text-right">
            <a href="<?php echo url_for('degustation_visualisation', $d)?>"class="btn btn-sm btn-default"><?= ($d->etape)? DegustationEtapes::$libelles_short[$d->etape] : 'Lots'  ?></a>
        </td>
    </tr>
<?php endforeach; ?>
<tbody>
</table>
<?php endif; ?>
