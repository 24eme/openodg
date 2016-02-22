<?php include_partial('tirage/step', array('step' => 'validation', 'tirage' => $tirage)) ?>
<div class="page-header">
    <h2>Validation de votre déclaration</h2>
</div>

<form id="validation-form" role="form" action="<?php echo url_for("tirage_validation", $tirage) ?>" method="post">
    <?php echo $form->renderHiddenFields() ?>
    <?php echo $form->renderGlobalErrors() ?>

    <?php if(isset($form["date"])): ?>
    <div class="row">
        <div class="form-group <?php if ($form["date"]->hasError()): ?>has-error<?php endif; ?>">
            <?php if ($form["date"]->hasError()): ?>                            
                <div class="alert alert-danger" role="alert"><?php echo $form["date"]->getError(); ?></div>
            <?php endif; ?>
            <?php echo $form["date"]->renderLabel(null, array("class" => "col-xs-4 control-label")); ?>
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

    <?php if($validation->hasPoints()): ?>
    <?php endif; ?>

    <div class="row row-margin">
        <div class="col-xs-12">
            <?php include_partial('tirage/recap', array('tirage' => $tirage)); ?>
        </div>
    </div>

    <?php  if (count($validation->getPoints(DrevValidation::TYPE_ENGAGEMENT)) > 0): ?>
        <h2 class="h3">J'ai pris connaissance des pièces à fournir</h2>
        <div class="alert" role="alert" id="engagements">
            <div class="form-group">
           
                <div class="alert alert-danger <?php if(!$form->hasErrors()): ?>hidden<?php endif; ?>" role="alert">
                    <ul class="error_list">
                        <li class="text-left">Vous devez cocher pour valider votre déclaration.</li>
                    </ul>
                </div>
                
                <?php foreach ($validation->getPoints(DrevValidation::TYPE_ENGAGEMENT) as $engagement): ?>
                <div class="checkbox-container <?php if ($form['engagement_' . $engagement->getCode()]->hasError()): ?>has-error<?php endif; ?>">
                    <div class="checkbox<?php if($engagement->getCode() == DRevDocuments::DOC_DR && $drev->hasDr()): ?> disabled<?php endif; ?>">
                        <label>
                            <?php 
                                if ($engagement->getCode() == DRevDocuments::DOC_DR && $drev->hasDr()) {
                                    echo $form['engagement_' . $engagement->getCode()]->render(array('checked' => 'checked'));
                                } else {
                                    echo $form['engagement_' . $engagement->getCode()]->render();
                                }
                            ?>
                            <?php echo $engagement->getRawValue()->getMessage() ?>
                            <?php if ($engagement->getCode() == DRevDocuments::DOC_DR && $drev->hasDr()): ?>- <a href="<?php echo $drev->getAttachmentUri('DR.pdf'); ?>" target="_blank"><small>Télécharger ma DR</small></a><?php endif; ?>
                        </label>
                    </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
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
            <button type="button" id="btn-validation-document" data-toggle="modal" data-target="#tirage-confirmation-validation" <?php if($validation->hasErreurs()): ?>disabled="disabled"<?php endif; ?> class="btn btn-default btn-lg btn-upper"><span class="glyphicon glyphicon-check"></span>&nbsp;&nbsp;Valider la déclaration</button>
        </div>
    </div>

</form>
<?php include_partial('tirage/popupConfirmationValidation'); ?>