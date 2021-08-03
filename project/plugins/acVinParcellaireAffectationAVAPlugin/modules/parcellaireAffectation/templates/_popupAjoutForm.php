<div class="modal fade" id="<?php if (!isset($html_id)): ?>popupForm<?php else: ?><?php echo $html_id ?><?php endif; ?>" role="dialog" aria-labelledby="Ajouter un produit" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="<?php echo $url ?>" role="form" class="form-horizontal">
                <?php echo $form->renderHiddenFields(); ?>
                <?php echo $form->renderGlobalErrors(); ?>
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>

                    <h2 class="modal-title" id="myModalLabel">Ajouter une parcelle&nbsp;<?php echo ($appellation == ParcellaireAffectationClient::APPELLATION_VTSGN)? 'd\'AOC Alsace blanc' : '';  ?></h2>
                </div>
                <div class="modal-body">
                    <?php include_partial('parcellaireAffectation/parcelleForm', array('form' => $form,'appellation' => $appellation)); ?>
                </div>
                <div class="modal-footer">
                    <a class="btn btn-danger btn pull-left" data-dismiss="modal">Annuler</a>
                    <button type="submit" class="btn btn-default btn pull-right">Ajouter la parcelle</button>
                </div>
            </form>
        </div>
    </div>
</div>
