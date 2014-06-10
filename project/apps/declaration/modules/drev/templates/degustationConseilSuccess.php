<?php include_partial('drev/step', array('step' => 'degustation_conseil', 'drev' => $drev)) ?>

<form method="post" action="" role="form" class="form-horizontal">
    <?php echo $form->renderHiddenFields(); ?>
    <?php echo $form->renderGlobalErrors(); ?>

    <h3>AOC Alsace</h3>
    <div class="form-group">
        <?php echo $form['cuve_alsace']->renderError(); ?>
        <?php echo $form['cuve_alsace']->renderLabel(); ?>
        <?php echo $form['cuve_alsace']->render(); ?>
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
    <?php echo $form['cuve_vtsgn']->renderError(); ?>
    <?php echo $form['cuve_vtsgn']->renderLabel(); ?>
    <?php echo $form['cuve_vtsgn']->render(); ?>
    </div>

    <a href="<?php echo url_for("drev_revendication", $drev) ?>" class="btn btn-primary btn-lg pull-left">Étape précedente</a>
    <button type="submit" class="btn btn-primary btn-lg pull-right">Étape suivante</a>
</form>


