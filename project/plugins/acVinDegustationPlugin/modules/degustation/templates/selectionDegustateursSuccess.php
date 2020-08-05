<?php use_helper("Date"); ?>

<?php include_partial('degustation/breadcrumb', array('degustation' => $degustation)); ?>
<?php include_partial('degustation/step', array('degustation' => $degustation, 'active' => DegustationEtapes::ETAPE_DEGUSTATEURS)); ?>


<div class="page-header no-border">
    <h2>Sélection des dégustateurs</h2>
</div>
<p>Sélectionnez l'ensemble des dégustateurs en vue de leurs participations à la dégustation</p>
