<div class="modal fade" id="parcellaire-confirmation-validation" role="dialog" aria-labelledby="Confirmation de validation" aria-hidden="true">


    <div class="modal-dialog">
        <div class="modal-content">
            <form role="form" action="<?php echo url_for("parcellaire_validation", $parcellaire) ?>" method="post">
                <?php echo $form->renderHiddenFields() ?>
                <?php echo $form->renderGlobalErrors() ?>
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title" id="myModalLabel">Validation de votre déclaration d'affectation parcellaire</h4>
                </div>               

                <div class="modal-body">
                    <div class="row form-group">
                        <label style="font-weight: normal; padding-left:10px">
                            <div class="row">
                                <div class="col-xs-1" style="padding-left:20px; padding-top:10px;" >
                                    <input id="" type="checkbox" checked="checked" name="<?php echo $form['autorisation_acheteur']->getName(); ?>">
                                </div>
                                <div class="col-xs-10">
                                    Je souhaite transmettre à mes acheteurs les données de cette déclaration pour les lieux dits qui les concernent
                                </div>
                            </div>
                        </label>

                    </div>
                    <p>Confirmez-vous la validation de votre déclaration d'affectation parcellaire ?</p>
                </div>
                <div class="modal-footer">
                    <a class="btn btn-danger btn pull-left" data-dismiss="modal">Annuler</a>
                    <button type="submit" class="btn btn-default btn pull-right">Confirmer</button>
                </div>
            </form>
        </div>
    </div>
</div>