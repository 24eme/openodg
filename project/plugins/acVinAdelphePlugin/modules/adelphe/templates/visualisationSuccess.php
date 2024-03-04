<?php include_partial('adelphe/breadcrumb', array('adelphe' => $adelphe )); ?>

<div class="page-header">
    <h2>Visualisation <small>de votre déclaration</small></h2>
</div>

<?php include_partial('adelphe/recap', array('adelphe' => $adelphe)); ?>

<?php if ($sf_user->isAdmin()): ?>
<div class="row row-margin row-button pt-3">
    <div class="col-xs-12 text-right">
          <a type="submit" class="btn btn-warning btn-upper" href="<?php echo url_for('adelphe_reouvrir', $adelphe) ?>">Réouvrir la déclaration</a>
    </div>
</div>
<?php endif; ?>
