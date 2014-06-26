<?php include_partial('drev/step', array('step' => 'controle_externe', 'drev' => $drev)) ?>

<form method="post" action="" role="form" class="form-horizontal">
    <?php echo $form->renderHiddenFields(); ?>
    <?php echo $form->renderGlobalErrors(); ?>

    <h3>AOC Alsace</h3>
    <div class="form-group">
        <?php echo $form[DRev::BOUTEILLE_ALSACE]["date"]->renderError(); ?>
        <?php echo $form[DRev::BOUTEILLE_ALSACE]["date"]->renderLabel(); ?>
        <?php echo $form[DRev::BOUTEILLE_ALSACE]["date"]->render(); ?>
    </div>

    <h3>AOC Alsace Grands Crus</h3>
    <div class="form-group">
        <?php echo $form[DRev::BOUTEILLE_GRDCRU]["date"]->renderError(); ?>
        <?php echo $form[DRev::BOUTEILLE_GRDCRU]["date"]->renderLabel(); ?>
        <?php echo $form[DRev::BOUTEILLE_GRDCRU]["date"]->render(); ?>
    </div>
    
    <h3>VT / SGN</h3>
    <div class="form-group">
    <?php echo $form[DRev::BOUTEILLE_VTSGN]["date"]->renderError(); ?>
    <?php echo $form[DRev::BOUTEILLE_VTSGN]["date"]->renderLabel(); ?>
    <?php echo $form[DRev::BOUTEILLE_VTSGN]["date"]->render(); ?>
    </div>
        
    <p class="clearfix">
        <a href="<?php echo url_for("drev_degustation_conseil", $drev) ?>" class="btn btn-primary btn-lg pull-left">Étape précedente</a>
        <button type="submit" class="btn btn-primary btn-lg pull-right">Étape suivante</button>
    </p>
</form>