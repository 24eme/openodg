<?php include_partial('drev/step', array('step' => 'degustation_conseil', 'drev' => $drev)) ?>

<?php include_partial('drev/stepDegustationConseil', array('step' => 'prelevement', 'drev' => $drev)) ?>

<form method="post" action="" role="form" class="form-horizontal">
    <?php echo $form->renderHiddenFields(); ?>
    <?php echo $form->renderGlobalErrors(); ?>

    <h3>AOC Alsace</h3>
    <div class="form-group">
        <?php echo $form[DRev::CUVE_ALSACE]["date"]->renderError(); ?>
        <?php echo $form[DRev::CUVE_ALSACE]["date"]->renderLabel(); ?>
        <?php echo $form[DRev::CUVE_ALSACE]["date"]->render(); ?>
    </div>
    
    <h3>VT / SGN</h3>
    <div class="form-group">
      <div class="checkbox">
        <label>
          <input type="checkbox"> Demande de prélévement volontaire des VT / SGN
        </label>
      </div>
    </div>
    <div class="form-group">
    <?php echo $form[DRev::CUVE_VTSGN]["date"]->renderError(); ?>
    <?php echo $form[DRev::CUVE_VTSGN]["date"]->renderLabel(); ?>
    <?php echo $form[DRev::CUVE_VTSGN]["date"]->render(); ?>
    </div>

	<p class="clearfix">
    	<button type="submit" class="btn btn-warning pull-right">Valider et répartir les lots</button>
    </p>
    <p class="clearfix">
        <a href="<?php echo url_for("drev_revendication", $drev) ?>" class="btn btn-primary btn-lg pull-left">Étape précedente</a>
        <a href="<?php echo url_for("drev_controle_externe", $drev) ?>" class="btn btn-primary btn-lg pull-right">Étape suivante</a>
    </p>
</form>



