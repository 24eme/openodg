<?php include_partial('drev/step', array('step' => 'validation', 'drev' => $drev)) ?>

<div class="page-header no-border">
    <h2>Validation de votre déclaration</h2>
</div>

<form role="form" action="" method="post">
        <?php echo $form->renderHiddenFields(); ?>
        <?php echo $form->renderGlobalErrors(); ?>

        <?php if($validation->hasPoints()): ?>
            <?php include_partial('drev/pointsAttentions', array('drev' => $drev, 'validation' => $validation)); ?>
        <?php endif; ?>
        <?php include_partial('drev/recap', array('drev' => $drev)); ?>
        <?php include_partial('drev/engagements', array('drev' => $drev)); ?>

    <div class="row row-margin">
        <div class="col-xs-4">
            <a href="<?php echo url_for("drev_controle_externe", $drev) ?>" class="btn btn-primary btn-lg"><span class="eleganticon arrow_carrot-left pull-left"></span>Étape précédente</a>
        </div>
        <div class="col-xs-4 text-center">
            <a href="<?php echo url_for("drev_export_pdf", $drev) ?>" class="btn btn-warning btn-lg">
                <span class="glyphicon glyphicon-save"></span>
                Prévisualiser
            </a>
        </div>
        <div class="col-xs-4 text-right">
            <button type="submit" <?php if($validation->hasErreurs()): ?>disabled="disabled"<?php endif; ?> class="btn btn-default btn-lg">VALIDER&nbsp;&nbsp;<span class="glyphicon glyphicon-check"></span></button>
        </div>
    </div>
</form>