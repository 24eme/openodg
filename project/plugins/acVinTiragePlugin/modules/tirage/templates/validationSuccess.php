<?php include_partial('tirage/breadcrumb', array('tirage' => $tirage )); ?>
<?php include_partial('tirage/step', array('step' => 'validation', 'tirage' => $tirage)) ?>
<div class="page-header">
    <h2>Validation de votre déclaration</h2>
</div>

<form id="validation-form" role="form" action="<?php echo url_for("tirage_validation", $tirage) ?>" method="post">
    <?php echo $form->renderHiddenFields() ?>
    <?php echo $form->renderGlobalErrors() ?>

    <?php if($sf_user->hasFlash('success')): ?><div class="alert alert-success"><?php echo $sf_user->getFlash('success'); ?></div><?php endif; ?>

    <?php if($sf_user->hasFlash('error')): ?><div class="alert alert-danger"><?php echo $sf_user->getFlash('error'); ?></div><?php endif; ?>

    <?php if (isset($validation) && $validation->hasPoints()): ?>
        <?php include_partial('tirage/pointsAttentions', array('tirage' => $tirage, 'validation' => $validation)); ?>
    <?php endif; ?>

    <?php include_partial('tirage/recap', array('tirage' => $tirage)); ?>

    <?php  if (!$tirage->isPapier() && count($validation->getEngagements()) > 0): ?>
        <h2 class="h3" style="margin-bottom:0;">J'ai pris connaissance des pièces à fournir</h2>
        <div class="alert" role="alert" id="engagements" style="padding-top:0;">
            <div class="form-group">

                <?php foreach ($validation->getPoints(TirageValidation::TYPE_ENGAGEMENT) as $engagement): ?>
                    <?php if($engagement->getCode() == TirageDocuments::AUTORISATION_PARTAGE_CIVA): ?>
                        <?php continue; ?>
                    <?php endif ?>

                    <div class="checkbox-container <?php if ($form['engagement_' . $engagement->getCode()]->hasError()): ?>has-error<?php endif; ?>">
                        <div class="checkbox<?php if($engagement->getCode() == TirageDocuments::DOC_PRODUCTEUR && $tirage->hasDr()): ?> disabled<?php endif; ?>">
                            <label>
                                <?php
                                    if ($engagement->getCode() == TirageDocuments::DOC_PRODUCTEUR && $tirage->hasDr()) {
                                        echo $form['engagement_' . $engagement->getCode()]->render(array('checked' => 'checked'));
                                    } elseif($engagement->getCode() == TirageDocuments::DOC_PRODUCTEUR && !$tirage->hasDr()) {
                                        echo $form['engagement_' . $engagement->getCode()]->render(array('class' => 'hidden'));

                                    } else {
                                        $svRecu = ($tirage->hasSV())? array('checked' => 'checked') : array();
                                        echo $form['engagement_' . $engagement->getCode()]->render($svRecu);

                                    }
                                ?>
                                <?php if ($engagement->getCode() != TirageDocuments::DOC_PRODUCTEUR || $tirage->hasDr()): ?>
                                <?php echo $engagement->getRawValue()->getMessage() ?>
                                <?php endif; ?>
                                <?php if ($engagement->getCode() == TirageDocuments::DOC_PRODUCTEUR && $tirage->hasDr()): ?>- <a href="<?php echo url_for("tirage_dr_pdf", $tirage); ?>" class="btn-link" target="_blank"><small>Voir ma Déclaration de récolte associée</small></a>
                                <?php endif; ?>
                                <?php if ($engagement->getCode() == TirageDocuments::DOC_PRODUCTEUR && !$tirage->hasDr()): ?>
                                    <a href="<?php echo url_for("tirage_dr_recuperation", $tirage) ?>">☐ Récupérer ma DR <?php echo $tirage->getCampagneDR(); ?> depuis le CIVA</a>
                                <?php endif; ?>
                            </label>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <div class="alert alert-danger <?php if(!$form->hasErrors()): ?>hidden<?php endif; ?>" role="alert" style="margin-top:30px;">
                        <ul class="error_list">
                            <li class="text-left">Vous devez cocher les deux cases pour valider votre déclaration.</li>
                        </ul>
                    </div>
            </div>
        </div>
    <?php endif; ?>

    <?php  if (!$tirage->isPapier() && count($validation->getEngagements()) > 0 && array_key_exists(TirageDocuments::AUTORISATION_PARTAGE_CIVA, $validation->getEngagements()->getRawValue())): ?>
        <h2 class="h3">Autorisation de partage des données</h2>
        <div class="alert" role="alert" id="engagements_partage" style="padding-top:0;">
            <div class="form-group">
                <?php foreach ($validation->getEngagements() as $engagement): ?>
                    <?php if (strpos($engagement->getCode(), "AUTORISATION_PARTAGE") !== 0): continue; endif ?>
                    <div class="checkbox-container<?php if ($form['engagement_' . $engagement->getCode()]->hasError()): ?> has-error<?php endif; ?>">
                        <div class="checkbox">
                            <label>
                                <?php echo $form['engagement_' . $engagement->getCode()]->render(['checked' => 'checked']); ?>
                                <?php echo $engagement->getMessage(); ?>
                            </label>
                        </div>
                    </div>
                <?php endforeach ?>
            </div>
        </div>
    <?php endif ?>

    <?php if(isset($form["date"])): ?>
    <div class="row">
        <div class="form-group <?php if ($form["date"]->hasError()): ?>has-error<?php endif; ?>">
            <?php if ($form["date"]->hasError()): ?>
                <div class="alert alert-danger" role="alert"><?php echo $form["date"]->getError(); ?></div>
            <?php endif; ?>
            <?php echo $form["date"]->renderLabel(null, array("class" => "col-xs-4 col-xs-offset-4 control-label")); ?>
            <div class="col-xs-4">
                <div class="input-group date-picker-all-days">
                    <?php echo $form["date"]->render(array("class" => "form-control")); ?>
                    <div class="input-group-addon">
                        <span class="glyphicon glyphicon-calendar"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if(isset($form['commentaire'])): ?>
        <h3>Commentaire interne <small>(seulement visible par l'ODG)</small></h3>
        <?php echo $form['commentaire']->render(array('class' => 'form-control text-left', "")) ?>
    <?php endif; ?>

    <div class="row row-margin row-button">
        <div class="col-xs-4">
            <a href="<?php echo url_for("tirage_lots", $tirage) ?>" class="btn btn-primary btn-lg btn-upper"><span class="eleganticon arrow_carrot-left"></span>&nbsp;&nbsp;Retourner <small>à l'étape précédente</small></a>
        </div>
        <div class="col-xs-4 text-center">
            <a href="<?php echo url_for("tirage_export_pdf", $tirage) ?>" class="btn btn-warning btn-lg">
                <span class="glyphicon glyphicon-file"></span>&nbsp;&nbsp;Prévisualiser
            </a>
        </div>
        <div class="col-xs-4 text-right">
            <button type="button" id="btn-validation-document" data-toggle="modal" data-target="#tirage-confirmation-validation" <?php if($validation->hasErreurs() && (!$sf_user->isAdmin() || $validation->hasFatales())): ?>disabled="disabled"<?php endif; ?> class="btn btn-default btn-lg btn-upper"><span class="glyphicon glyphicon-check"></span>&nbsp;&nbsp;Valider la déclaration</button>
        </div>
    </div>

</form>
<?php include_partial('tirage/popupConfirmationValidation'); ?>
