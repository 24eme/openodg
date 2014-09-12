<?php include_partial('drevmarc/step', array('step' => 'validation', 'drevmarc' => $drevmarc)) ?>

<form role="form" action="<?php echo url_for("drevmarc_validation", $drevmarc) ?>" method="post">
    <div class="frame">	
        <?php echo $form->renderHiddenFields() ?>
        <?php echo $form->renderGlobalErrors() ?>
        <?php if ($validation->hasPoints()): ?>
            <?php //include_partial('drev/pointsAttentions', array('drev' => $drev, 'validation' => $validation)); ?>
        <?php endif; ?>
        <?php include_partial('drevmarc/recap', array('drevmarc' => $drevmarc)); ?>
        <?php //include_partial('drev/engagements', array('drev' => $drev)); ?>
    </div>

    <div class="row row-margin">
        <div class="col-xs-4">
            <a href="<?php echo url_for("drev_controle_externe", $drevmarc) ?>" class="btn btn-primary btn-lg btn-block"><span class="eleganticon arrow_carrot-left pull-left"></span>Étape précédente</a>
        </div>
        <div class="col-xs-4 text-center">
            <a href="<?php echo url_for("drevmarc_export_pdf", $drevmarc) ?>" class="btn btn-warning btn-lg">
                <span class="glyphicon glyphicon-save"></span>
                Prévisualiser
            </a>
        </div>
        <div class="col-xs-4">
            <button type="submit" class="btn btn-default btn-lg btn-block">VALIDER&nbsp;&nbsp;<span class="glyphicon glyphicon-check"></span></button>
        </div>
    </div>

</form>