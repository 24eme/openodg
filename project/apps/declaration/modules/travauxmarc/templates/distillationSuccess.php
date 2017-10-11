<?php include_partial('travauxmarc/breadcrumb', array('travauxmarc' => $travauxmarc )); ?>
<?php include_partial('travauxmarc/step', array('step' => 'distillation', 'travauxmarc' => $travauxmarc)) ?>

<div class="page-header">
    <h2>Distillation</h2>
</div>

<form role="form" action="<?php echo url_for("travauxmarc_distillation", $travauxmarc) ?>" method="post" class="ajaxForm" id="form_travauxmarc_distillation">
    <?php echo $form->renderHiddenFields() ?>
    <?php echo $form->renderGlobalErrors() ?>

    <div class="row row-margin row-button">
        <div class="col-xs-6"><a href="<?php echo url_for("travauxmarc_fournisseurs", $travauxmarc) ?>" class="btn btn-primary btn-lg btn-upper"><span class="eleganticon arrow_carrot-left"></span>&nbsp;&nbsp;Retourner <small>à l'étape précédente</small></a></div>
        <div class="col-xs-6 text-right"><button type="submit" class="btn btn-default btn-lg btn-upper">Valider et continuer&nbsp;&nbsp;<span class="eleganticon arrow_carrot-right"></span></button></div>
    </div>
</form>
