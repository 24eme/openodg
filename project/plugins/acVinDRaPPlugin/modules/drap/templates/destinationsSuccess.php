<?php use_helper('Float'); ?>

<?php if(isset($coop)): ?>
    <?php include_partial('parcellaireAffectationCoop/headerDeclaration', ['coop' => $coop, 'declaration' => $drap]); ?>
<?php else: ?>
    <?php include_partial('drap/breadcrumb', array('drap' => $drap)); ?>
<?php endif; ?>

<?php include_partial('drap/step', array('step' => 'destinations', 'drap' => $drap)) ?>
<div class="page-header">
    <h2>Parcelles irrigables sur votre exploitation <br/><small>Merci d'indiquer le type de matériel et de ressource utilisés sur chaque parcelle irrigable</small></h2>
</div>

<form action="<?php echo url_for("drap_destinations", $drap) ?>" method="post" class="form-horizontal">
    <?php include_partial('drap/formDestinations', ['drap' => $drap, 'form' => $form]); ?>

	<div class="row row-margin row-button">
        <div class="col-xs-4"><a href="<?php echo url_for("drap_parcelles", $drap); ?>" class="btn btn-default btn-upper"><span class="glyphicon glyphicon-chevron-left"></span> Retourner à l'étape précédente</a></div>
        <div class="col-xs-4 text-center">
            <button type="submit" name="saveandquit" value="1" class="btn btn-default">Enregistrer en brouillon</button>
        </div>
        <div class="col-xs-4 text-right"><button type="submit" class="btn btn-primary btn-upper">Continuer <span class="glyphicon glyphicon-chevron-right"></span></button></div>
    </div>
</form>

<?php if(isset($coop)): ?>
    <?php include_partial('parcellaireAffectationCoop/footerDeclaration', ['coop' => $coop, 'declaration' => $drap]); ?>
<?php endif; ?>
