<?php use_helper('Date') ?>

<?php include_partial('parcellaireAffectation/breadcrumb', array('parcellaireAffectation' => $parcellaireAffectation)); ?>

<?php include_partial('parcellaireAffectation/step', array('step' => 'denominations', 'parcellaireAffectation' => $parcellaireAffectation)) ?>

<div class="page-header no-border">
    <h2>Identification des parcelles affectées
    <?php if($parcellaireAffectation->isPapier()): ?>
    <small class="pull-right"><span class="glyphicon glyphicon-file"></span> Déclaration papier</small>
    <?php endif; ?>
    </h2>
</div>

<?php if ($sf_user->hasFlash('notice')): ?>
    <div class="alert alert-success" role="alert"><?php echo $sf_user->getFlash('notice') ?></div>
<?php endif; ?>

<p>Veuillez activer les dénominations complémentaires pour lesquelles vous souhaitez y déclarer vos parcelles.</p>
     
<form action="<?php echo url_for("parcellaireaffectation_denominations", $parcellaireAffectation) ?>" method="post" class="form-horizontal">
	<?php echo $form->renderHiddenFields(); ?>
    <?php echo $form->renderGlobalErrors(); ?>
    
	<?php if(isset($form["date_papier"])): ?>
    <div class="row">
        <div class="form-group <?php if ($form["date_papier"]->hasError()): ?>has-error<?php endif; ?>">
            <?php if ($form["date_papier"]->hasError()): ?>
                <div class="alert alert-danger" role="alert"><?php echo $form["date_papier"]->getError(); ?></div>
            <?php endif; ?>
            <?php echo $form["date_papier"]->renderLabel(null, array("class" => "col-xs-4 control-label")); ?>
            <div class="col-xs-4">
                <div class="input-group date-picker">
                    <?php echo $form["date_papier"]->render(array("class" => "form-control")); ?>
                    <div class="input-group-addon">
                        <span class="glyphicon glyphicon-calendar"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <table class="table table-bordered table-condensed table-striped duplicateChoicesTable tableParcellaire">
		<thead>
        	<tr>
                <th class="col-xs-2">Dénominations complémentaire</th>
                <th class="col-xs-1">Affectation?</th>
            </tr>
		</thead>
		<tbody>
			<?php echo $form["dgc"]->render(); ?>
        </tbody>
	</table>
    
	<div class="row row-margin row-button">
        <div class="col-xs-4"><a href="<?php echo url_for("parcellaireaffectation_exploitation", $parcellaireAffectation); ?>" class="btn btn-default btn-upper"><span class="glyphicon glyphicon-chevron-left"></span> Retour</a></div>
        <div class="col-xs-4 text-center">
        </div>
        <div class="col-xs-4 text-right"><button type="submit" class="btn btn-primary btn-upper"  id="btn-validation-document">Valider <span class="glyphicon glyphicon-chevron-right"></span></button></div>
    </div>
</form>
</div>
