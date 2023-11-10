<?php use_helper('Float'); ?>
<?php use_helper('Lot'); ?>
<?php use_helper('Date') ?>

<?php include_partial('degustation/breadcrumb'); ?>

<div class="page-header no-border">
    <h2>Lots en attente</h2>
</div>

<ul class="nav nav-tabs" role="tablist">
    <li role="presentation" class="active"><a href="#degustation" aria-controls="degustation" role="tab" data-toggle="tab"> Lots<?php if(isset($lotsTournees)): ?> prélevés <?php endif; ?> en attente de dégustation <span class="badge"><?= count($lotsDegustation) ?></span></a></li>
    <?php if(isset($lotsTournee)): ?>
    <li role="presentation"><a href="#tournee" aria-controls="tournee" role="tab" data-toggle="tab">Lots en attente de tournée de prélevement <span class="badge"><?= count($lotsTournee) ?></span></a></li>
    <?php endif; ?>
 </ul>

<div class="tab-content" style="padding-top: 15px;">
    <div role="tabpanel" class="tab-pane active" id="degustation">
        <?php include_partial('degustation/lots', array('lots' => $lotsDegustation)); ?>
    </div>
    <?php if(isset($lotsTournee)): ?>
    <div role="tabpanel" class="tab-pane" id="tournee">
        <?php include_partial('degustation/lots', array('lots' => $lotsTournee)); ?>
    </div>
    <?php endif; ?>
</div>
