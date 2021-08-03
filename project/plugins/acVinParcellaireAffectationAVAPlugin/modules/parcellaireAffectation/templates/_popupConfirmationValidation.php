<div class="modal fade" id="parcellaire-confirmation-validation" role="dialog" aria-labelledby="Confirmation de validation" aria-hidden="true">


    <div class="modal-dialog">
        <div class="modal-content">
            <form role="form" action="<?php echo url_for("parcellaire_validation", $parcellaire) ?>" method="post">
                <?php echo $form->renderHiddenFields() ?>
                <?php echo $form->renderGlobalErrors() ?>
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title" id="myModalLabel">Validation de votre déclaration d'<?php if ($parcellaire->isIntentionCremant()): ?>intention de production<?php else: ?>affectation parcellaire<?php endif; ?> <?php if($parcellaire->isParcellaireCremant()): ?><?php if($parcellaire->isIntentionCremant()): ?>AOC Crémant d'Alsace<?php else: ?>Crémant<?php endif; ?><?php endif; ?></h4>
                </div>               

                <div class="modal-body">
                    <?php if(isset($form["autorisation_acheteur"])): ?>
                    <div class="row form-group">
                        <label style="font-weight: normal; padding-left:10px">
                            <div class="row">
                                <div class="col-xs-1" style="padding-left:20px; padding-top:10px;" >
                                    <input id="" type="checkbox" checked="checked" name="<?php echo $form['autorisation_acheteur']->renderName(); ?>" />
                                </div>
                                <div class="col-xs-10">
                                    Je souhaite transmettre à mes acheteurs les données de cette déclaration pour les lieux dits qui les concernent
                                </div>
                            </div>
                        </label>
                    </div>
                    <?php endif; ?>
                    <?php if(isset($form["date"])): ?>
                        <div class="row">
                            <div class="form-group <?php if ($form["date"]->hasError()): ?>has-error<?php endif; ?>">
                                <?php if ($form["date"]->hasError()): ?>                            
                                    <div class="alert alert-danger" role="alert"><?php echo $form["date"]->getError(); ?></div>
                                <?php endif; ?>
                                <?php echo $form["date"]->renderLabel(null, array("class" => "col-xs-6 control-label")); ?>
                                <div class="col-xs-6">
                                    <div class="input-group date-picker-all-days">
                                        <?php echo $form["date"]->render(array("class" => "form-control", "required" => "required")); ?>
                                        <div class="input-group-addon">
                                            <span class="glyphicon glyphicon-calendar"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                     <p>Confirmez-vous la validation de votre déclaration d'<?php if ($parcellaire->isIntentionCremant()): ?>intention de production<?php else: ?>affectation parcellaire<?php endif; ?> <?php if($parcellaire->isParcellaireCremant()): ?><?php if($parcellaire->isIntentionCremant()): ?>AOC Crémant d'Alsace<?php else: ?>Crémant<?php endif; ?><?php endif; ?> ?</p>
                </div>
                <div class="modal-footer">
                    <a class="btn btn-danger btn pull-left" data-dismiss="modal">Annuler</a>
                    <button type="submit" class="btn btn-default btn pull-right">Confirmer</button>
                </div>
            </form>
        </div>
    </div>
</div>