<div class="page-header no-border">
    <h2><?php echo $parcellaireAffectation->declarant->raison_sociale ?> - Saisie de la déclaration d'affectation parcellaire</h2>
</div>
<form id="validation-form" action="" method="post" class="form-horizontal">
    <?php include_partial("parcellaireAffectation/formAffectations", array('parcellaireAffectation' => $parcellaireAffectation, 'form' => $form)); ?>
    <div class="row row-margin row-button">
        <div class="col-xs-4"><a href="<?php echo url_for("parcellaireaffectationcoop_liste", array('sf_subject' => $etablissement, 'periode' => $periode)) ?>" class="btn btn-default btn-upper"><span class="glyphicon glyphicon-chevron-left"></span> Retour</a></div>
        <div class="col-xs-4 text-center">
        </div>
        <div class="col-xs-4 text-right"><button id="btn-validation-document" data-toggle="modal" data-target="#parcellaireaffectation-confirmation-validation" class="btn btn-primary"><span class="glyphicon glyphicon-check"></span> Valider</button></div>
    </div>

    <?php include_partial('parcellaireAffectationCoop/popupConfirmationValidation', array('form' => $form, 'parcellaireAffectation' => $parcellaireAffectation)); ?>
</form>
