<?php include_partial('parcellaireAffectation/breadcrumb', array('parcellaire' => $parcellaire )); ?>
<?php include_partial('parcellaireAffectation/step', array('step' => 'validation', 'parcellaire' => $parcellaire)) ?>
<div class="page-header">
    <h2>Validation de votre déclaration d'<?php if ($parcellaire->isIntentionCremant()): ?>intention de production<?php else: ?>affectation parcellaire<?php endif; ?> <?php if($parcellaire->isParcellaireCremant()): ?><?php if($parcellaire->isIntentionCremant()): ?>AOC Crémant d'Alsace<?php else: ?>Crémant<?php endif; ?><?php endif; ?></h2>
</div>

<div class="row col-xs-12">
    <h3>Merci de vérifier votre déclaration d'<?php if ($parcellaire->isIntentionCremant()): ?>intention de production<?php else: ?>affectation parcellaire<?php endif; ?><?php if($parcellaire->isParcellaireCremant()): ?><?php if($parcellaire->isIntentionCremant()): ?> AOC Crémant d'Alsace<?php else: ?> Crémant<?php endif; ?><?php endif; ?>&nbsp;<?php echo $parcellaire->campagne; ?></h3>
    <p class="text-muted">Une version PDF est téléchargeable en bas de cet écran.</p>
</div>

<?php if (isset($validation) && $validation->hasPoints()): ?>
    <?php include_partial('parcellaireAffectation/pointsAttentions', array('parcellaire' => $parcellaire, 'validation' => $validation)); ?>
<?php endif; ?>
<?php include_partial('parcellaireAffectation/recap', array('parcellaire' => $parcellaire, 'diff' => 1)); ?>

    <?php  if ($parcellaire->hasVtsgn()): ?>
    <div class="alert" role="alert" id="engagements">
        <div class="form-group">
            <div class="alert alert-danger hidden" role="alert">
                <ul class="error_list">
                    <li class="text-left">Vous devez vous engager sur ce point afin de pouvoir valider votre déclaration.</li>
                </ul>
            </div>

            <div class="checkbox-container">
                <div class="checkbox">
                    <label><input type="checkbox" name="parcellaire_validation[engagement_vtsgn]" /> Je m'engage à respecter les conditions de production des mentions VT/SGN et les modalités de contrôle qui y sont liées. </label>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="row row-margin row-button">
        <div class="col-xs-4">
            <a href="<?php echo url_for("parcellaire_acheteurs", $parcellaire); ?>" class="btn btn-primary btn-lg btn-upper"><span class="eleganticon arrow_carrot-left"></span>&nbsp;&nbsp;Précédent</a>
        </div>
        <div class="col-xs-4 text-center">
        <a href="<?php echo url_for("parcellaire_export_pdf", $parcellaire) ?>" class="btn btn-warning btn-lg">
            <span class="glyphicon glyphicon-file"></span>&nbsp;&nbsp;Prévisualiser
        </a>
        </div>
        <div class="col-xs-4 text-right">
            <button id="btn-validation-document-parcellaire" type="button" data-toggle="modal" data-target="#parcellaire-confirmation-validation" <?php if ($validation->hasErreurs()): ?>disabled="disabled"<?php endif; ?> class="btn btn-default btn-lg btn-upper"><span class="glyphicon glyphicon-check"></span>&nbsp;&nbsp;Valider la déclaration</button>
        </div>
    </div>
<?php if (!$validation->hasErreurs()): ?>
    <?php include_partial('parcellaireAffectation/popupConfirmationValidation', array('form' => $form,'parcellaire' => $parcellaire)); ?>
<?php endif; ?>
