<?php use_helper('Date') ?>

<?php include_partial('parcellaireIntentionAffectation/breadcrumb', array('parcellaireIntentionAffectation' => $parcellaireIntentionAffectation)); ?>


<div class="page-header no-border">
    <h2>Identification des parcelles affectées
    <small class="pull-right"><span class="glyphicon glyphicon-file"></span> Déclaration papier</small>
    </h2>
</div>

<?php if ($sf_user->hasFlash('notice')): ?>
    <div class="alert alert-success" role="alert"><?php echo $sf_user->getFlash('notice') ?></div>
<?php endif; ?>

<p>Veuillez activer les dénominations complémentaires pour lesquelles vous souhaitez y déclarer vos parcelles.</p>
     
<form action="<?php echo url_for("parcellaireintentionaffectation_edit", array("sf_subject" => $etablissement, "campagne" => $campagne)) ?>" method="post" class="form-horizontal">
	<?php echo $form->renderHiddenFields(); ?>
    <?php echo $form->renderGlobalErrors(); ?>
    
    
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
        <div class="col-xs-4"><a href="#" class="btn btn-default btn-upper"><span class="glyphicon glyphicon-chevron-left"></span> Annuler</a></div>
        <div class="col-xs-4 text-center">
        </div>
        <div class="col-xs-4 text-right"><button type="submit" class="btn btn-primary btn-upper"  id="btn-validation-document">Valider <span class="glyphicon glyphicon-chevron-right"></span></button></div>
    </div>
</form>
</div>
