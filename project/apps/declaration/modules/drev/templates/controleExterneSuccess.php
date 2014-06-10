<?php include_partial('drev/step', array('step' => 'controle_externe', 'drev' => $drev)) ?>

<form method="post" action="" role="form" class="form-horizontal">
    <?php echo $form->renderHiddenFields(); ?>
    <?php echo $form->renderGlobalErrors(); ?>

    <h3>AOC Alsace</h3>
    <div class="form-group">
        <?php echo $form['bouteille_alsace']->renderError(); ?>
        <?php echo $form['bouteille_alsace']->renderLabel(); ?>
        <?php echo $form['bouteille_alsace']->render(); ?>
    </div>

    <h3>AOC Alsace Grands Crus</h3>
    <div class="form-group">
        <?php echo $form['bouteille_alsace_grdcru']->renderError(); ?>
        <?php echo $form['bouteille_alsace_grdcru']->renderLabel(); ?>
        <?php echo $form['bouteille_alsace_grdcru']->render(); ?>
    </div>
    
    <h3>VT / SGN</h3>
    <div class="form-group">
    <?php echo $form['bouteille_vtsgn']->renderError(); ?>
    <?php echo $form['bouteille_vtsgn']->renderLabel(); ?>
    <?php echo $form['bouteille_vtsgn']->render(); ?>
    </div>

    <a href="<?php echo url_for("drev_degustation_conseil", $drev) ?>" class="btn btn-primary btn-lg pull-left">Étape précedente</a>
    <button type="submit" class="btn btn-primary btn-lg pull-right">Étape suivante</a>
</form>