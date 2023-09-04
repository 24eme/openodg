<div class="modal" id="modal-aleatoire-aleatoire-renforce" role="dialog" aria-labelledby="Ajouter un operateur" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4>Sélectionnez un opérateur à prélever pour la dégustation en aléatoire ou aléatoire renforcé</h4>
            </div>
            <div class="modal-body">
                <form action="<?php echo url_for("degustation_selection_operateurs", $degustation) ?>" method="post" class="form-horizontal degustation prelevements selectionlots">
                    <?php echo $form->renderHiddenFields(); ?>

                    <div class="bg-danger">
                        <?php echo $form->renderGlobalErrors(); ?>
                    </div>

                    <?php echo $form['identifiant']->render() ?>
                    <?php echo $form['initial_type']->render() ?>
                </form>
            </div>
            <div class="modal-footer">
                <a class="btn btn-default btn pull-left" data-dismiss="modal">Annuler</a>
            </div>
        </div>
    </div>
</div>
