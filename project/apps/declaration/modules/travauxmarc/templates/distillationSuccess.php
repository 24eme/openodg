<?php include_partial('travauxmarc/breadcrumb', array('travauxmarc' => $travauxmarc )); ?>
<?php include_partial('travauxmarc/step', array('step' => 'distillation', 'travauxmarc' => $travauxmarc)) ?>

<div class="page-header">
    <h2>Distillation</h2>
</div>

<form role="form" action="<?php echo url_for("travauxmarc_distillation", $travauxmarc) ?>" method="post" class="ajaxForm form-horizontal" id="form_travauxmarc_distillation">
    <?php echo $form->renderHiddenFields() ?>
    <?php echo $form->renderGlobalErrors() ?>

    <div class="form-group">
        <?php echo $form['date_distillation']->renderLabel("Date de distillation :", array('class' => 'control-label col-sm-4', 'style' => 'text-align: left; font-weight: normal;')); ?>
        <div class="col-sm-4">
            <div class="input-group date-picker-all-days">
                <?php echo $form['date_distillation']->render(array('class' => 'form-control', 'placeholder' => 'Date de distillation')); ?>
                <div class="input-group-addon">
                    <span class="glyphicon-calendar glyphicon"></span>
                </div>
            </div>
        </div>
    </div>

    <div class="form-group">
        <?php echo $form['distillation_prestataire']->renderLabel("Distillation par un prestataire :", array('class' => 'control-label col-sm-4', 'style' => 'text-align: left; font-weight: normal;')); ?>
        <div class="col-sm-6">
            <?php echo $form['distillation_prestataire']->render(); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form['alambic_connu']->renderLabel("L'alambic&nbsp;utilisé&nbsp;est&nbsp;décrit&nbsp;dans&nbsp;la
DI&nbsp;:", array('class' => 'control-label col-sm-4', 'style' => 'text-align: left; font-weight: normal;')); ?>
        <div class="col-sm-6">
            <?php echo $form['alambic_connu']->render(); ?>
        </div>
    </div>

    <div class="form-group">
        <?php echo $form['adresse_distillation']['adresse']->renderLabel("Adresse de distillation :", array('class' => 'control-label col-sm-4', 'style' => 'text-align: left; font-weight: normal;')); ?>
        <div class="col-sm-4">
            <?php echo $form['adresse_distillation']['adresse']->render(array('class' => 'form-control', 'placeholder' => 'Adresse')); ?>
        </div>
        <div class="col-sm-2">
            <?php echo $form['adresse_distillation']['code_postal']->render(array('class' => 'form-control', 'placeholder' => 'Code postal')); ?>
        </div>
        <div class="col-sm-2">
            <?php echo $form['adresse_distillation']['commune']->render(array('class' => 'form-control', 'placeholder' => 'Commune')); ?>
        </div>
    </div>

    <div class="row row-margin row-button">
        <div class="col-xs-6"><a href="<?php echo url_for("travauxmarc_fournisseurs", $travauxmarc) ?>" class="btn btn-primary btn-lg btn-upper"><span class="eleganticon arrow_carrot-left"></span>&nbsp;&nbsp;Retourner <small>à l'étape précédente</small></a></div>
        <div class="col-xs-6 text-right"><button type="submit" class="btn btn-default btn-lg btn-upper">Valider et continuer&nbsp;&nbsp;<span class="eleganticon arrow_carrot-right"></span></button></div>
    </div>
</form>
