<?php use_helper('Date') ?>

<?php include_partial('parcellaireAffectation/breadcrumb', array('parcellaireAffectation' => $parcellaireAffectation)); ?>

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


<form id="validation-form" action="<?php echo url_for("parcellaireAffectation_create", array('sf_subject' => $etablissement, 'campagne' => $campagne, 'papier' => $papier)) ?>" method="post" class="form-horizontal">
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
    
    <div class="row">
        <div class="form-group <?php if ($form["dgc"]->hasError()): ?>has-error<?php endif; ?>">
        	 <div class="col-sm-12">
             <div class="checkbox">
            	<?php echo $form["dgc"]->renderLabel(null, array("class" => "control-label")); ?>
            	<?php echo $form["dgc"]->render(); ?>
            </div>
            </div>
        </div>
    </div>
    
	<div class="row row-margin row-button">
        <div class="col-xs-4"><a href="<?php echo url_for("declaration_etablissement", array('identifiant' => $parcellaireAffectation->identifiant)); ?>" class="btn btn-default btn-upper"><span class="glyphicon glyphicon-chevron-left"></span> Retour</a></div>
        <div class="col-xs-4 text-center">
            <?php if($parcellaireAffectation->isValidee()): ?>
                <a href="<?php echo url_for('ParcellaireAffectation_export_pdf', $parcellaireAffectation) ?>" class="btn btn-success">
                    <span class="glyphicon glyphicon-file"></span>&nbsp;&nbsp;Visualiser
                </a>
            <?php endif; ?>
        </div>
        <div class="col-xs-4 text-right"><button type="submit" class="btn btn-primary btn-upper"  id="btn-validation-document">Valider <span class="glyphicon glyphicon-chevron-right"></span></button></div>
    </div>
</form>
</div>
