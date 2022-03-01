<?php include_partial('degustation/breadcrumb', array('degustation' => $degustation)); ?>
<?php include_partial('degustation/step', array('degustation' => $degustation, 'active' => DegustationEtapes::ETAPE_LOTS)); ?>

<div class="page-header no-border">
    <h2>Sélection des lots</h2>
</div>

<p class="alert alert-danger">Des lots ont été prélevés pour cette dégustation.</p>

<p>Pour ajouter un lot dans cette dégustation, vous devenez donc passer par <a href="<?php echo url_for('degustation_prelevables'); ?>">l'historique du lot que vous souhaitez ajouter</a> (ou <a href="<?php echo url_for('degustation_preleve', array('id' => $degustation->_id)); ?>">retirer les prélèvements de cette dégustation</a>)</p>