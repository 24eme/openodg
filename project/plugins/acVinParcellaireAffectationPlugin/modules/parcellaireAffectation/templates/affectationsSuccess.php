<?php use_helper('Date') ?>

<?php include_partial('parcellaireAffectation/breadcrumb', array('parcellaireAffectation' => $parcellaireAffectation)); ?>
<?php include_partial('parcellaireAffectation/step', array('step' => 'affectations', 'parcellaireAffectation' => $parcellaireAffectation)) ?>
<div class="page-header no-border">
    <h2>Déclaration d'affectation parcellaire de l'AOC de Côtes de Provence</h2>
</div>
<form id="validation-form" action="<?php echo url_for("parcellaireaffectation_affectations", $parcellaireAffectation) ?>" method="post" class="form-horizontal">
    <?php include_partial("parcellaireAffectation/formAffectations", array('parcellaireAffectation' => $parcellaireAffectation, 'form' => $form)); ?>
    <div class="row row-margin row-button">
        <div class="col-xs-4"><a href="<?php echo url_for("parcellaireaffectation_exploitation", $parcellaireAffectation) ?>" class="btn btn-default btn-upper"><span class="glyphicon glyphicon-chevron-left"></span> Retour</a></div>
        <div class="col-xs-4 text-center">
        </div>
        <div class="col-xs-4 text-right"><button type="submit" class="btn btn-primary btn-upper">Valider <span class="glyphicon glyphicon-chevron-right"></span></button></div>
    </div>
</form>
