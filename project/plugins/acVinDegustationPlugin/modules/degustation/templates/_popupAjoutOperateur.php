<div class="modal" id="modal-aleatoire-aleatoire-renforce" role="dialog" aria-labelledby="Ajouter un operateur" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4>Sélectionnez un opérateur à prélever</h4>
            </div>
            <div class="modal-body">
                <form id="form_degustation_ajout_operateur" action="<?php echo url_for("degustation_selection_operateurs", $degustation) ?>" method="post" class="form-horizontal">
                    <?php echo $form->renderHiddenFields(); ?>

                    <div class="bg-danger">
                        <?php echo $form->renderGlobalErrors(); ?>
                    </div>

                    <div class="form-group">
                        <?php echo $form['initial_type']->renderLabel(null, ['class' => 'col-sm-3 control-label']) ?>
                        <div class="col-sm-9">
                            <?php echo $form['initial_type']->render() ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <?php echo $form['identifiant']->renderLabel(null, ['class' => 'col-sm-3 control-label']) ?>
                        <div class="col-sm-9">
                            <?php echo $form['identifiant']->render() ?>
                        </div>
                    </div>

                </form>
            </div>
            <div class="modal-footer">
                <a class="btn btn-default btn pull-left" data-dismiss="modal">Annuler</a>

                <button form="form_degustation_ajout_operateur" class="btn btn-primary" type="submit">Ajouter l'opérateur</a>
            </div>
        </div>
    </div>
</div>
