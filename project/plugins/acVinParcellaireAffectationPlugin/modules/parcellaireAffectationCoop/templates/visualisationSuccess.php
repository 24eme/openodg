<?php include_partial('parcellaireAffectationCoop/breadcrumb', array('parcellaireAffectationCoop' => $parcellaireAffectationCoop, 'declaration' => $parcellaireAffectation)); ?>
<?php include_partial('parcellaireAffectationCoop/step', array('step' => 'saisies', 'parcellaireAffectationCoop' => $parcellaireAffectationCoop)) ?>

<div class="panel panel-default">
    <div class="panel-heading">
        <?php include_partial('parcellaireAffectationCoop/headerSaisie', ['declaration' => $parcellaireAffectation, 'parcellaireAffectationCoop' => $parcellaireAffectationCoop, 'hasForm' => true]); ?>
    </div>
    <div class="panel-body">
        <div class="page-header no-border mt-0">
            <h3 class="mt-2">Déclaration d'affectation parcellaire <?php echo $parcellaireAffectation->getPeriode() ?></h3>
        </div>

<?php include_partial('parcellaireAffectation/recap', array('parcellaireAffectation' => $parcellaireAffectation)); ?>

<?php if($parcellaireAffectation->observations): ?>
    <div class="panel panel-default">
        <div class="panel-body">
            <div class="row">
                    <div class="col-xs-3">
                        <h3>Observations :</h3>
                    </div>
                     <div class="col-xs-9">
                        <?php echo nl2br($parcellaireAffectation->observations); ?>
                     </div>
            </div>
        </div>
   </div>
<?php endif; ?>
</div>
<div class="panel-footer">
<div class="row row-margin row-button">
    <div class="col-xs-4">
        <a href="<?php echo url_for("parcellaireaffectationcoop_liste", $parcellaireAffectationCoop) ?>" class="btn btn-default btn-upper"><span class="glyphicon glyphicon-chevron-left"></span> Retour à la liste</a>
    </div>
    <div class="col-xs-4 text-center">
            <a href="<?php echo url_for('parcellaireaffectation_export_pdf', $parcellaireAffectation) ?>" class="btn btn-success">
                <span class="glyphicon glyphicon-file"></span>&nbsp;&nbsp;Visualiser
            </a>
    </div>
    <div class="col-xs-4 text-right">
    </div>
</div>
</div>
</div>
