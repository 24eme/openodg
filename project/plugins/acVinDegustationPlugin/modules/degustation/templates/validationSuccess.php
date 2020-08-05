<?php use_helper("Date"); ?>

<?php include_partial('degustation/breadcrumb', array('degustation' => $degustation)); ?>
<?php include_partial('degustation/step', array('degustation' => $degustation, 'active' => DegustationEtapes::ETAPE_VALIDATION)); ?>


<div class="page-header no-border">
    <h2>Validation de la dégustation</h2>
</div>
<p>Récapitulatif de la dégustation à valider</p>
