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
        <?php if ($sf_user->isAdmin()): ?>
            <div class="btn-group">
                <a href="<?php echo url_for("parcellaire_export_pdf", $parcellaire) ?>" class="btn btn-warning btn-lg">
                    <span class="glyphicon glyphicon-file"></span>&nbsp;&nbsp;Prévisualiser
                </a>
                <?php if (count($parcellaire->getAcheteursByCVI())): ?>
                    <button type="button" class="btn btn-warning btn-lg dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <span class="caret"></span>
                        <span class="sr-only">Toggle Dropdown</span>
                    </button>
                    <ul class="dropdown-menu">
                        <li>
                            <a href="<?php echo url_for("parcellaire_export_pdf", $parcellaire) ?>">Global (PDF)</a>
                        </li>
                        <li>
                            <a href="<?php echo url_for("parcellaire_export_csv", $parcellaire) ?>">Global (CSV)</a>
                        </li>
                        <?php foreach ($parcellaire->getAcheteursByCVI() as $cvi => $acheteur): ?>
                        <li>
                            <a href="<?php echo url_for("parcellaire_export_pdf", $parcellaire) ?>?cvi=<?php echo $cvi ?>"><?php echo $acheteur->nom ?> (PDF)</a>
                        </li>
                        <li>
                            <a href="<?php echo url_for("parcellaire_export_csv", $parcellaire) ?>?cvi=<?php echo $cvi ?>"><?php echo $acheteur->nom ?> (CSV)</a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <a href="<?php echo url_for("parcellaire_export_pdf", $parcellaire) ?>" class="btn btn-warning btn-lg">
                <span class="glyphicon glyphicon-file"></span>&nbsp;&nbsp;Prévisualiser
            </a>
        <?php endif ?>
        </div>
        <div class="col-xs-4 text-right">
            <button id="btn-validation-document-parcellaire" type="button" data-toggle="modal" data-target="#parcellaire-confirmation-validation" <?php if ($validation->hasErreurs()): ?>disabled="disabled"<?php endif; ?> class="btn btn-default btn-lg btn-upper"><span class="glyphicon glyphicon-check"></span>&nbsp;&nbsp;Valider la déclaration</button>
        </div>
    </div>
<?php if (!$validation->hasErreurs()): ?>
    <?php include_partial('parcellaireAffectation/popupConfirmationValidation', array('form' => $form,'parcellaire' => $parcellaire)); ?>
<?php endif; ?>
