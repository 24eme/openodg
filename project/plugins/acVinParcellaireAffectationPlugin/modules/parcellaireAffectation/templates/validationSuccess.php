<?php use_helper('Date') ?>

<?php if(isset($coop)): ?>
    <?php include_partial('parcellaireAffectationCoop/headerDeclaration', ['coop' => $coop, 'declaration' => $parcellaireAffectation]); ?>
<?php else: ?>
    <?php include_partial('parcellaireAffectation/breadcrumb', array('parcellaireAffectation' => $parcellaireAffectation)); ?>
<?php endif; ?>

<?php include_partial('parcellaireAffectation/step', array('step' => 'validation', 'parcellaireAffectation' => $parcellaireAffectation)) ?>

<?php if (isset($validation) && $validation->hasPoints()): ?>
    <?php include_partial('parcellaireAffectation/pointsAttentions', ['validation' => $validation]); ?>
<?php endif; ?>

<form role="form" action="<?php echo url_for('parcellaireaffectation_validation', $parcellaireAffectation) ?>" method="post" id="validation-form">
    <?php echo $form->renderHiddenFields(); ?>
    <?php echo $form->renderGlobalErrors(); ?>

    <?php if(isset($form["date"])): ?>
        <?php if ($form["date"]->getError()): ?>
            <div class="alert alert-danger" role="alert"><?php echo $form["date"]->getError(); ?></div>
        <?php endif; ?>
    <div class="row">
        <div class="form-group <?php if ($form["date"]->hasError()): ?>has-error<?php endif; ?>">

            <?php echo $form["date"]->renderLabel(null, array("class" => "col-xs-4 control-label")); ?>
            <div class="col-xs-4">
                <div class="input-group date-picker">
                    <?php echo $form["date"]->render(array("class" => "form-control")); ?>
                    <div class="input-group-addon">
                        <span class="glyphicon glyphicon-calendar"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="page-header no-border">
        <h2>Validation de votre déclaration</h2>
    </div>

    <?php include_partial('parcellaireAffectation/recap', array('parcellaireAffectation' => $parcellaireAffectation, 'coop' => $coop)); ?>

    <div class="row">
        <div class="col-xs-10"></div>
        <div class="col-xs-2 mb-2">
            <a href="<?php echo url_for('parcellaire_potentiel_visualisation', array('id' => $parcellaireAffectation->getParcellaire()->_id)); ?>">Voir le détail du potentiel</a>
        </div>
    </div>
    <div class="panel panel-default">
        <div class="panel-body">
            <div class="row">
                <div class="form-group <?php if ($form["observations"]->hasError()): ?>has-error<?php endif; ?>">
                    <div class="col-xs-3">
                        <h3>Observations :</h3>
                    </div>
                     <div class="col-xs-9">
                        <?php echo $form['observations']->renderError(); ?>
                        <?php echo $form['observations']->render(); ?>
                     </div>
                 </div>
             </div>
        </div>
   </div>
    <div style="padding-top: 10px;" class="row row-margin row-button">
        <div class="col-xs-4">
        	<a href="<?php echo url_for("parcellaireaffectation_affectations", $parcellaireAffectation) ?>" class="btn btn-default btn-upper"><span class="glyphicon glyphicon-chevron-left"></span> Retourner à l'étape précédente</a>
        </div>
        <div class="col-xs-4 text-center">
            <a href="<?php echo url_for('parcellaireaffectation_export_pdf', $parcellaireAffectation) ?>" class="btn btn-primary">
                <span class="glyphicon glyphicon-file"></span>&nbsp;&nbsp;Prévisualiser
            </a>
        </div>
        <div class="col-xs-4 text-right">
            <?php if(count($destinatairesIncomplete)): ?>
            <button type="button" data-toggle="modal" data-target="#parcellaireaffectation-information-incomplete" <?php if (isset($validation) && $validation->hasErreurs()): ?>disabled="disabled"<?php endif; ?> class="btn btn-success btn-upper"><span class="glyphicon glyphicon-check"></span>&nbsp;&nbsp;Terminer votre déclaration</button>
            <?php else: ?>
            <button type="button" id="btn-validation-document" data-toggle="modal" data-target="#parcellaireaffectation-confirmation-validation" <?php if (isset($validation) && $validation->hasErreurs()): ?>disabled="disabled"<?php endif; ?> class="btn btn-success btn-upper"><span class="glyphicon glyphicon-check"></span>&nbsp;&nbsp;Valider votre déclaration</button>
            <?php endif; ?>
        </div>
    </div>
    <?php if (!isset($validation) || !$validation->hasErreurs()): ?>
	<?php include_partial('parcellaireAffectation/popupConfirmationValidation', array('form' => $form)); ?>
	<?php endif; ?>
</form>

<?php if(isset($coop)): ?>
    <?php include_partial('parcellaireAffectationCoop/footerDeclaration', ['coop' => $coop, 'declaration' => $parcellaireAffectation]); ?>
<?php endif; ?>

<?php if(isset($form["signataire"]) && $form["signataire"]->hasError()): ?>
<script type="text/javascript">
$('#parcellaireaffectation-confirmation-validation').modal('show')
</script>
<?php endif; ?>

<?php if(count($destinatairesIncomplete)): ?>
    <div class="modal fade" id="parcellaireaffectation-information-incomplete" role="dialog" aria-labelledby="Confirmation de validation" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title" id="myModalLabel">Déclaration d'affectation parcellaire partagée</h4>
                </div>
                <div class="modal-body">
                    <p>Cette déclaration est partagée avec d'autres caves coopératives qui n'ont pas encore affecté leurs parcelles.</p>
                    <p>Elle sera validée lorsque ces autres caves auront également effectué leur saisie.</p>
                </div>
                <div class="modal-footer">
                    <button type="submit" form="validation-form" class="btn btn-success btn pull-right">Continuer</button>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>
